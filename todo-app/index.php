<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Firebase Todos App (Drag & Drop & Debug)</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f9f9f9; margin: 0; padding: 20px; }
    h1 { color: #333; }
    #controls { margin-bottom: 20px; }
    #status { background: #fff3cd; border: 1px solid #ffeeba; border-radius: 4px; padding: 10px; max-height: 150px; overflow-y: auto; margin-top: 20px; }
    #todos-list { list-style: none; padding: 0; }
    #todos-list li { background: #fff; margin-bottom: 10px; padding: 10px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; align-items: center; cursor: move; }
    #todos-list li span { flex: 1; margin-left: 8px; }
    #todos-list li .completed { text-decoration: line-through; color: #888; }
    #todos-list li button { margin-left: 10px; background-color: #28a745; border: none; padding: 4px 8px; color: white; border-radius: 4px; cursor: pointer; }
    #todos-list li button.delete { background-color: #dc3545; }
  </style>
</head>
<body>
  <h1>Firebase Todos App</h1>
  <div id="controls">
    <label for="limit-input">Load limit:</label>
    <input type="number" id="limit-input" value="50" min="1" max="500" style="width:60px;" />
    <button id="apply-limit">Apply</button>
  </div>
  <form id="todo-form" style="margin-bottom:20px; display:flex;">
    <input type="text" id="todo-input" placeholder="New todo text" required style="flex:1; padding:8px; border:1px solid #ccc; border-radius:4px 0 0 4px;" />
    <button type="submit">Add Todo</button>
  </form>
  <ul id="todos-list"></ul>
  <div id="status"></div>

  <script type="module">
    import { initializeApp } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-app.js";
    import {
      getFirestore,
      collection,
      addDoc,
      onSnapshot,
      updateDoc,
      deleteDoc,
      doc,
      query,
      orderBy,
      limit as fsLimit,
      writeBatch,
      getDocs
    } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-firestore.js";

    // Initialize Firebase
    const app = initializeApp({
      apiKey: "AIzaSyDvcG0BwCBOlli38DbJIqsTFDFDN898gIw",
      authDomain: "ben-flow-test.firebaseapp.com",
      projectId: "ben-flow-test",
      storageBucket: "ben-flow-test.firebasestorage.app",
      messagingSenderId: "537365778977",
      appId: "1:537365778977:web:aac079139808d188b9c960"
    });
    const db = getFirestore(app);
    const todosRef = collection(db, 'todos');

    // UI refs
    const statusEl = document.getElementById('status');
    const limitInput = document.getElementById('limit-input');
    const applyBtn = document.getElementById('apply-limit');
    const form = document.getElementById('todo-form');
    const input = document.getElementById('todo-input');
    const listEl = document.getElementById('todos-list');

    let unsubscribe = null;

    // Logger
    function log(msg) {
      const time = new Date().toISOString();
      console.log(`[${time}] ${msg}`);
      const entry = document.createElement('div');
      entry.textContent = `[${time}] ${msg}`;
      statusEl.appendChild(entry);
      statusEl.scrollTop = statusEl.scrollHeight;
    }

    // Add Todo
    form.addEventListener('submit', async e => {
      e.preventDefault();
      const text = input.value.trim();
      if (!text) return log('Cannot add empty todo');
      try {
        const initialOrder = Date.now();
        const ref = await addDoc(todosRef, { text, completed: false, order: initialOrder });
        log(`Added [${ref.id}] with order ${initialOrder}`);
        input.value = '';
      } catch (err) {
        log(`Add error: ${err.message}`);
      }
    });

    // Reordering
    async function reorderList(docs, draggedId, targetId) {
      const idArray = docs.map(d => d.id);
      const from = idArray.indexOf(draggedId);
      const to = idArray.indexOf(targetId);
      idArray.splice(to, 0, idArray.splice(from, 1)[0]);
      const batch = writeBatch(db);
      idArray.forEach((id, idx) => {
        batch.update(doc(db, 'todos', id), { order: idx });
      });
      await batch.commit();
      log('Reorder committed');
    }

    function enableDragDrop(li, id, docs) {
      li.draggable = true;
      li.addEventListener('dragstart', e => {
        e.dataTransfer.setData('text/plain', id);
        log(`Drag start: ${id}`);
      });
      li.addEventListener('dragover', e => e.preventDefault());
      li.addEventListener('drop', async e => {
        e.preventDefault();
        const draggedId = e.dataTransfer.getData('text/plain');
        if (draggedId !== id) await reorderList(docs, draggedId, id);
      });
    }

    // Initialize real-time listener
    function initListener(limitCount) {
      if (unsubscribe) unsubscribe();
      const q = query(todosRef, orderBy('order'), fsLimit(limitCount));
      unsubscribe = onSnapshot(q, snapshot => {
        const docs = snapshot.docs.map(docSnap => ({ id: docSnap.id, data: docSnap.data() }));
        log(`Loaded ${docs.length} items`);
        listEl.innerHTML = '';
        docs.forEach(({ id, data }) => {
          const li = document.createElement('li');
          const cb = document.createElement('input'); cb.type = 'checkbox'; cb.checked = data.completed;
          cb.addEventListener('change', async () => {
            try {
              await updateDoc(doc(db, 'todos', id), { completed: cb.checked });
              log(`Toggled [${id}] -> ${cb.checked}`);
            } catch (e) { log(`Toggle error: ${e.message}`); }
          });
          const span = document.createElement('span'); span.textContent = data.text;
          if (data.completed) span.classList.add('completed');

          const edit = document.createElement('button'); edit.textContent = 'Edit';
          edit.addEventListener('click', async () => {
            const nt = prompt('Edit todo', data.text);
            if (nt && nt.trim()) {
              try {
                await updateDoc(doc(db, 'todos', id), { text: nt.trim() });
                log(`Edited [${id}]`);
              } catch (e) { log(`Edit error: ${e.message}`); }
            } else log('Edit cancelled');
          });

          const del = document.createElement('button'); del.textContent = 'Delete'; del.className = 'delete';
          del.addEventListener('click', async () => {
            if (confirm('Delete?')) {
              try {
                await deleteDoc(doc(db, 'todos', id));
                log(`Deleted [${id}]`);
              } catch (e) { log(`Delete error: ${e.message}`); }
            }
          });

          li.append(cb, span, edit, del);
          listEl.appendChild(li);
          enableDragDrop(li, id, docs);
        });
        // After rendering, debug full collection
        debugAllDocs(docs.map(d => d.id));
      }, err => log(`Snapshot error: ${err.message}`));
    }

    // Debug function: log docs missing fields or not loaded
    async function debugAllDocs(loadedIds) {
      try {
        const allSnap = await getDocs(todosRef);
        log(`Total docs in collection: ${allSnap.size}`);
        allSnap.forEach(docSnap => {
          const id = docSnap.id;
          const data = docSnap.data();
          const missing = [];
          if (!('text' in data)) missing.push('text');
          if (!('completed' in data)) missing.push('completed');
          if (!('order' in data)) missing.push('order');
          if (missing.length) log(`Doc [${id}] missing fields: ${missing.join(', ')}`);
          if (!loadedIds.includes(id)) log(`Doc [${id}] not loaded (outside limit or filtered)`);
        });
      } catch (e) {
        log(`Debug load error: ${e.message}`);
      }
    }

    // Controls
    applyBtn.addEventListener('click', () => {
      initListener(parseInt(limitInput.value, 10) || 50);
      log(`Limit set to ${limitInput.value}`);
    });

    // Start app
    initListener(parseInt(limitInput.value, 10) || 50);
    log('App ready & debugging enabled');
  </script>
</body>
</html>

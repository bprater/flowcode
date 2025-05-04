<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Firebase Todos App (Drag & Drop)</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f9f9f9; margin: 0; padding: 20px; }
    h1 { color: #333; }
    #controls, #debug-controls { margin-bottom: 20px; }
    button { padding: 6px 12px; margin-right: 8px; border: none; background: #007bff; color: white; border-radius: 4px; cursor: pointer; font-size: 14px; }
    button:hover { opacity: 0.9; }
    #status { background: #fff3cd; border: 1px solid #ffeeba; border-radius: 4px; padding: 10px; max-height: 150px; overflow-y: auto; margin-bottom: 10px; font-size: 14px; }
    #todos-list { list-style: none; padding: 0; }
    #todos-list li { background: #fff; margin-bottom: 10px; padding: 10px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; align-items: center; cursor: move; }
    #todos-list li span { flex: 1; margin-left: 8px; }
    #todos-list li .completed { text-decoration: line-through; color: #888; }
    #todos-list li button { margin-left: 10px; background-color: #28a745; }
    #todos-list li button.delete { background-color: #dc3545; }
  </style>
</head>
<body>
  <h1>Firebase Todos App (Drag & Drop)</h1>
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
      getFirestore, collection, addDoc, onSnapshot,
      updateDoc, deleteDoc, doc, query,
      orderBy, limit as fsLimit, writeBatch
    } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-firestore.js";

    // Init Firebase
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

    // Logging
    function log(msg) {
      const time = new Date().toISOString();
      console.log(`[${time}] ${msg}`);
      const entry = document.createElement('div');
      entry.textContent = `[${time}] ${msg}`;
      statusEl.appendChild(entry);
      statusEl.scrollTop = statusEl.scrollHeight;
    }

    // Add todo handler
    form.addEventListener('submit', async e => {
      e.preventDefault();
      const text = input.value.trim();
      if (!text) return log('Cannot add empty todo');
      try {
        // Use timestamp for initial order
        const initialOrder = Date.now();
        const ref = await addDoc(todosRef, { text, completed: false, order: initialOrder });
        log(`Added [${ref.id}]`);
        input.value = '';
      } catch (err) {
        log(`Add error: ${err.message}`);
      }
    });

    // Drag & drop reorder
    async function reorderList(docs, draggedId, targetId) {
      const newOrder = docs.map(d => d.id);
      const from = newOrder.indexOf(draggedId);
      const to = newOrder.indexOf(targetId);
      newOrder.splice(to, 0, newOrder.splice(from, 1)[0]);

      // Batch update
      const batch = writeBatch(db);
      newOrder.forEach((id, idx) => {
        const docRef = doc(db, 'todos', id);
        batch.update(docRef, { order: idx });
      });
      await batch.commit();
      log('Reorder committed');
    }

    // Attach drag events
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
        if (draggedId !== id) {
          log(`Drop: ${draggedId} -> ${id}`);
          await reorderList(docs, draggedId, id);
        }
      });
    }

    // Initialize listener
    function initListener(limitCount) {
      if (unsubscribe) unsubscribe();
      const q = query(todosRef, orderBy('order'), fsLimit(limitCount));
      unsubscribe = onSnapshot(q, snapshot => {
        const docs = snapshot.docs.map(docSnap => ({ id: docSnap.id, data: docSnap.data() }));
        listEl.innerHTML = '';
        docs.forEach(({ id, data }) => {
          const li = document.createElement('li');
          const cb = document.createElement('input'); cb.type = 'checkbox'; cb.checked = data.completed;
          cb.addEventListener('change', async () => {
            try {
              await updateDoc(doc(db, 'todos', id), { completed: cb.checked });
              log(`Toggled [${id}] to ${cb.checked}`);
            } catch (e) { log(`Toggle error: ${e.message}`); }
          });
          const span = document.createElement('span'); span.textContent = data.text;
          if (data.completed) span.classList.add('completed');

          const edit = document.createElement('button'); edit.textContent = 'Edit';
          edit.addEventListener('click', async () => {
            const nt = prompt('Edit todo', data.text);
            if (nt && nt.trim()) {
              try { await updateDoc(doc(db, 'todos', id), { text: nt.trim() });
                log(`Edited [${id}]`);
              } catch (e) { log(`Edit error: ${e.message}`); }
            } else log('Edit cancelled');
          });

          const del = document.createElement('button'); del.textContent = 'Delete'; del.className = 'delete';
          del.addEventListener('click', async () => {
            if (confirm('Delete?')) {
              try { await deleteDoc(doc(db, 'todos', id)); log(`Deleted [${id}]`);
              } catch (e) { log(`Delete error: ${e.message}`); }
            }
          });

          li.append(cb, span, edit, del);
          listEl.appendChild(li);
          enableDragDrop(li, id, docs);
        });
        log(`Rendered ${docs.length} items`);
      }, err => log(`Snapshot error: ${err.message}`));
    }

    applyBtn.addEventListener('click', () => {
      const lim = parseInt(limitInput.value, 10) || 50;
      initListener(lim);
      log(`Applied limit ${lim}`);
    });

    // Start
    initListener(parseInt(limitInput.value, 10) || 50);
    log('App ready');
  </script>
</body>
</html>

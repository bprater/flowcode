<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Ben's Magic Todos</title>
  <!-- Tailwind CSS (class-based dark mode) -->
  <script>
    tailwind.config = { darkMode: 'class' };
  </script>
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Confetti -->
  <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>
</head>
<body class="bg-gradient-to-br from-blue-100 via-purple-100 to-pink-100 dark:from-gray-800 dark:via-gray-900 dark:to-black min-h-screen flex items-center justify-center p-4">
  <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-lg p-6 space-y-4">
    <div class="flex justify-between items-center">
      <h1 class="text-3xl font-extrabold text-gray-900 dark:text-gray-100">Ben's Magic Todos ✨</h1>
      <div class="flex space-x-2">
        <button id="theme-toggle" class="p-2 rounded-full bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 transition">
          <svg id="theme-icon" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-800 dark:text-gray-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"></svg>
        </button>
        <button id="debug-toggle" class="p-2 rounded-full bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 transition">🐞</button>
      </div>
    </div>

    <!-- Debug panel -->
    <div id="debug-panel" class="hidden bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-gray-100 p-4 rounded-lg border border-gray-300 dark:border-gray-700 max-h-40 overflow-y-auto font-mono text-sm"></div>

    <!-- Add form -->
    <form id="todo-form" class="flex">
      <input id="todo-input" type="text" placeholder="What's next?" class="flex-grow p-3 rounded-l-lg border-2 border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-purple-400 dark:focus:ring-purple-600" />
      <button type="submit" class="px-4 bg-purple-500 hover:bg-purple-600 text-white rounded-r-lg font-semibold transition">Add</button>
    </form>

    <!-- Todos list -->
    <ul id="todos-list" class="space-y-4"></ul>

    <!-- Progress bar -->
    <div>
      <div class="text-gray-700 dark:text-gray-300 font-medium mb-1">Progress</div>
      <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 overflow-hidden">
        <div id="progress-bar" class="bg-purple-500 h-4 w-0 transition-all"></div>
      </div>
    </div>
  </div>

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
      writeBatch,
      serverTimestamp,
      query,
      orderBy
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
    // Query descending for newest first
    const todosQuery = query(todosRef, orderBy('order', 'desc'));

    // DOM elements
    const form = document.getElementById('todo-form');
    const input = document.getElementById('todo-input');
    const listEl = document.getElementById('todos-list');
    const progressBar = document.getElementById('progress-bar');
    const themeToggle = document.getElementById('theme-toggle');
    const themeIcon = document.getElementById('theme-icon');
    const debugToggle = document.getElementById('debug-toggle');
    const debugPanel = document.getElementById('debug-panel');

    // Logger
    function log(msg) {
      const time = new Date().toLocaleTimeString();
      const entry = document.createElement('div'); entry.textContent = `[${time}] ${msg}`;
      debugPanel.appendChild(entry); debugPanel.scrollTop = debugPanel.scrollHeight;
      console.log(`[DEBUG] ${msg}`);
    }

    // Theme handling
    function updateThemeIcon() {
      if (document.documentElement.classList.contains('dark')) {
        themeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m8.66-12.34-.707.707m-14.14 0-.707-.707M21 12h-1M3 12H2m16.66 5.66-.707-.707m-14.14 0-.707.707"/>';
      } else {
        themeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>';
      }
    }
    // Load theme
    if (localStorage.getItem('theme') === 'dark') document.documentElement.classList.add('dark');
    else document.documentElement.classList.remove('dark');
    updateThemeIcon();
    themeToggle.addEventListener('click', () => {
      const isDark = document.documentElement.classList.toggle('dark');
      localStorage.setItem('theme', isDark ? 'dark' : 'light');
      updateThemeIcon();
    });
    debugToggle.addEventListener('click', () => debugPanel.classList.toggle('hidden'));

    // Confetti
    function celebrate() { confetti({ particleCount: 30, spread: 60, origin: { y: 0.6 } }); }

    // Reorder logic: assign high order index to top items
    async function reorderList(docs, draggedId, targetId) {
      // docs is in descending order
      const ids = docs.map(d => d.id);
      const from = ids.indexOf(draggedId), to = ids.indexOf(targetId);
      ids.splice(to, 0, ids.splice(from, 1)[0]);
      const batch = writeBatch(db);
      const N = ids.length;
      ids.forEach((id, idx) => {
        const newOrder = N - 1 - idx; // top item gets highest newOrder
        batch.update(doc(db, 'todos', id), { order: newOrder });
      });
      await batch.commit();
      log('Reorder committed');
    }

    // Enable drag handle on li
    function enableDrag(li, id, docs) {
      // Create handle
      const handle = document.createElement('span');
      handle.className = 'cursor-move text-gray-400 dark:text-gray-500 mr-2';
      handle.textContent = '☰';
      // Make handle draggable
      handle.draggable = true;
      handle.addEventListener('dragstart', e => {
        e.dataTransfer.setData('text/plain', id);
        log(`Drag start: ${id}`);
      });
      li.addEventListener('dragover', e => e.preventDefault());
      li.addEventListener('drop', async e => {
        e.preventDefault();
        const dragged = e.dataTransfer.getData('text/plain');
        if (dragged !== id) await reorderList(docs, dragged, id);
      });
      // Prepend handle
      li.prepend(handle);
    }

    // Add todo
    form.addEventListener('submit', async e => {
      e.preventDefault();
      const text = input.value.trim(); if (!text) return;
      // New items get highest timestamp, will appear top
      const order = Date.now();
      await addDoc(todosRef, { text, completed: false, order, createdAt: serverTimestamp() });
      log(`Added todo: "${text}"`);
      input.value = '';
      celebrate();
    });

    // Real-time listener
    onSnapshot(todosQuery, snap => {
      const docs = snap.docs.map(d => ({ id: d.id, data: d.data() }));
      let completedCount = 0;
      listEl.innerHTML = '';
      docs.forEach(({ id, data }) => {
        if (data.completed) completedCount++;
        const li = document.createElement('li');
        li.className = 'bg-gray-50 dark:bg-gray-700 p-4 rounded-lg flex items-center space-x-4 shadow transition';
        // Checkbox
        const cb = document.createElement('input');
        cb.type = 'checkbox'; cb.checked = data.completed;
        cb.className = 'h-5 w-5 text-purple-500';
        cb.addEventListener('change', async () => {
          await updateDoc(doc(db, 'todos', id), { completed: cb.checked });
          log(`Toggled [${id}] to ${cb.checked}`);
          if (cb.checked) celebrate();
        });
        // Text
        const span = document.createElement('span');
        span.textContent = data.text;
        span.className = `flex-1 text-lg ${data.completed ? 'line-through text-gray-400 dark:text-gray-500' : 'text-gray-900 dark:text-gray-100'}`;
        // Edit
        const edit = document.createElement('button');
        edit.textContent = '✏️';
        edit.className = 'text-lg';
        edit.addEventListener('click', async () => {
          const nt = prompt('Edit todo', data.text);
          if (nt && nt.trim()) { await updateDoc(doc(db, 'todos', id), { text: nt.trim() }); log(`Edited [${id}]`); }
        });
        // Delete
        const del = document.createElement('button');
        del.textContent = '🗑️';
        del.className = 'text-lg';
        del.addEventListener('click', async () => {
          if (confirm('Delete this todo?')) { await deleteDoc(doc(db, 'todos', id)); log(`Deleted [${id}]`); celebrate(); }
        });
        li.append(cb, span, edit, del);
        listEl.appendChild(li);
        enableDrag(li, id, docs);
      });
      // Progress
      const pct = docs.length ? (completedCount / docs.length) * 100 : 0;
      progressBar.style.width = `${pct}%`;
      log(`Rendered ${docs.length} todos, ${completedCount} completed (${pct.toFixed(1)}%)`);
    });
  </script>
</body>
</html>

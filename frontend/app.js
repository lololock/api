const app = Vue.createApp({
  data() {
    return {
      apiBaseUrl: 'http://api.localhost/tasks',
      tasks: [],
      newTitle: ''
    };
  },

  methods: {
    async fetchTasks() {
      const res = await fetch(this.apiBaseUrl);
      const data = await res.json();
      // Convertir done en nombre 
      this.tasks = data.map(t => ({ ...t, done: Number(t.done) }));
    },

async createTask() {
  if (!this.newTitle.trim()) return;

  const payload = {
    title: this.newTitle,
    done: 0   // ← IMPORTANT : nombre, pas string
  };

  const res = await fetch(this.apiBaseUrl, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  });

  let newTask = await res.json();

  // Sécurisation : on force done en nombre
  newTask.done = Number(newTask.done);

  this.tasks.push(newTask);
  this.newTitle = '';
},


    async toggleDone(task) {
      const updated = { ...task, done: task.done ? 0 : 1 };

      await fetch(`${this.apiBaseUrl}/${task.id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        
        body: JSON.stringify(updated)
      });

      task.done = updated.done;
    },

    async deleteTask(id) {
      await fetch(`${this.apiBaseUrl}/${id}`, { method: 'DELETE' });
      this.tasks = this.tasks.filter(t => t.id !== id);
    }
  },

  mounted() {
    this.fetchTasks();
  }
});

app.mount('#app');

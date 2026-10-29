'use strict';

Vue.createApp({
  data() {
    return {
      newTask: '',
      taskError: '',
      filter: 'all',
      nextId: 4,
      tasks: [
        { id: 1, name: 'Ôn lại các directive cơ bản của Vue.js', completed: true },
        { id: 2, name: 'Hoàn thành bài tập đặt vé xem phim', completed: false },
        { id: 3, name: 'Thực hành quản lý công việc với v-for', completed: false }
      ],
      filters: [
        { value: 'all', label: 'Tất cả' },
        { value: 'active', label: 'Chưa hoàn thành' },
        { value: 'completed', label: 'Đã hoàn thành' }
      ]
    };
  },
  computed: {
    totalTasks() {
      return this.tasks.length;
    },
    remainingTasks() {
      return this.tasks.filter(task => !task.completed).length;
    },
    completedTasks() {
      return this.totalTasks - this.remainingTasks;
    },
    filteredTasks() {
      if (this.filter === 'active') return this.tasks.filter(task => !task.completed);
      if (this.filter === 'completed') return this.tasks.filter(task => task.completed);
      return this.tasks;
    }
  },
  methods: {
    addTask() {
      const name = this.newTask.trim();
      if (!name) {
        this.taskError = 'Vui lòng nhập tên công việc, không để trống hoặc chỉ nhập khoảng trắng.';
        return;
      }
      this.tasks.push({ id: this.nextId++, name, completed: false });
      this.newTask = '';
      this.taskError = '';
      // Việc mới luôn được nhìn thấy sau khi thêm, kể cả khi đang lọc việc đã xong.
      if (this.filter === 'completed') this.filter = 'all';
    },
    deleteTask(id) {
      this.tasks = this.tasks.filter(task => task.id !== id);
    },
    clearCompleted() {
      this.tasks = this.tasks.filter(task => !task.completed);
    }
  }
}).mount('#tasks-app');

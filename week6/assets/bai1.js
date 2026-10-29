'use strict';

Vue.createApp({
  data() {
    return {
      student: {
        id: 'SV001',
        fullName: 'Nguyễn Văn An',
        className: 'CNTT K20',
        avatar: 'assets/avatar.svg',
        average: 8.2,
        isStudying: true,
        introduction: 'Xin chào! Mình là An, sinh viên ngành Công nghệ thông tin. Mình thích lập trình web và đang học Vue.js.'
      },
      showIntroduction: true
    };
  },
  computed: {
    validAverage() {
      return typeof this.student.average === 'number'
        && Number.isFinite(this.student.average)
        && this.student.average >= 0 && this.student.average <= 10;
    },
    gradeClass() {
      if (!this.validAverage) return 'neutral';
      if (this.student.average >= 8) return 'success';
      if (this.student.average >= 6.5) return 'info';
      if (this.student.average >= 5) return 'warning';
      return 'danger';
    }
  }
}).mount('#student-app');

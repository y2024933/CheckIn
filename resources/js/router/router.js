import VueRouter from 'vue-router';
import eheader from '../views/Header.vue';
// import store from '../store.js';
import htmlToPdf from '../function/htmlToPdf';
Vue.use(htmlToPdf)
var to_array = ["/", "/login"]; // 跳过登入画面
var router = new VueRouter({
  mode: 'history',
  routes: [
    {
      path: '/login',
      component: require('../views/Login').default
    },
    {
      path: '/checkin',
      component: require('../views/CheckIn.vue').default
    },
    {
      path: '/checklog',
      component: require('../views/CheckLog.vue').default
    },
    {
      path: '/report',
      component: require('../views/Report.vue').default
    },
    {
      path: '/createAccount',
      component: require('../views/CreateAccount.vue').default
    },
    {
      path: '/modifyAccount',
      component: require('../views/ModifyAccount.vue').default
    },
    {
      path: '/checkInSetting',
      component: require('../views/CheckInSetting.vue').default
    },
    {
      path: '/todo',
      component: require('../views/Todo.vue').default
    },
    {
      path: '/todayCheckIn',
      component: require('../views/TodayCheckIn.vue').default
    },
    {
      path: '/groupSetting',
      component: require('../views/GroupSetting.vue').default
    },
    // {
    //   path: '/header',
    //   component: require('../views/Header.vue').default,
    // },
  ]
});
router.beforeEach((to, from, next) => {
  if (to_array.indexOf(to.fullPath) === -1) {
    if (localStorage.getItem('token')) {
      axios({
        method: 'post',
        url: '/api/checkJwt',
        data: { href: to.fullPath },
      })
      .then((response) => {
        if (response.data.status != 'success') {
          router.app.$message.error(response.data.msg);
          localStorage.removeItem('token');
          clearAccount();
        } else {
          router.app.$store.commit('setAccount', response.data.msg)
          next();
        }
      });
    } else {
      router.app.$message.error('验证失败,请重新登入')
      clearAccount();
    }
  }
  next();
});

const clearAccount = function() {
  router.app.$store.commit('setAccount', {account: '', level: '', group: ''})
  router.app.$router.push({ path: "/login" });
}

new Vue({
  components: {
    eheader,
    htmlToPdf
  },
  data() {
    return {
    }
  },
  router,
  // store
}).$mount('#app');


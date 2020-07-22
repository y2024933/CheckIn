import Vue from 'vue';
import Vuex from 'vuex'
Vue.use(Vuex)

export const store = new Vuex.Store({
  state: {
    account: '',
    level: '',
    group: '',
    groupName: ''
  },
  getters  : {
    getAccount: state => state.account,
    getLevel: state => state.level,
    getGroup: state => state.group
  },
  mutations: {
    setAccount(state, account) {
      state.account   = account.account
      state.level     = account.level
      state.group     = account.group
      state.groupName = account.groupName
    }
  }
})

export default store;
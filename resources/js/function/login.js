export const getLogin = {
  methods: {
    login: function(form) {
      axios({
          method: 'post',
          url: '/api/login',
          data: {
            accountcode: form.accountcode,
            password: form.password,
          }
        })
        .then((response) => {
          if (response.data.status == "success") {
            localStorage.setItem('token_mobile', JSON.stringify(response.data.msg));
            window.location.href = '/myaccount';
          } else {
            this.$message.error(response.data.msg);
          }
        });
    },
    getAccountcode: function(para) {
      let getCredit = false
      if (typeof para === 'undefined') para = ''
      else para = `/${para}`
      axios({
          method: 'get',
          url: `/api/get_account${para}`
        })
        .then((response) => {
          if (response.data.status == "success") {
            this.info.vip = response.data.vip;
            this.info.vip_name = response.data.vip_name;
            this.info.acccode = response.data.accountcode;
            this.info.credit = parseFloat(response.data.creditlimit).toFixed(2)
            this.info.Loginis = true;
            this.info.NotLogin = false;
          } else {
            if (typeof para == 'undefined') {
              localStorage.removeItem('token_mobile');
              window.location.href = '/';
            } else {
              this.info.credit = parseFloat(response.data.creditlimit).toFixed(2)
            }
          }
        });
    }
  }
}
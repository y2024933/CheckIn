const vm = new Vue({
    data: {
        form: {
            accountcode: '',
            password: '',

        }
    },
    created() {
    },
    methods : {
        onSubmit : function(form) {
            console.log(form);
            axios({
              method: 'post',
              url: '/api/login',
              data: {
                accountcode: form.accountcode,
                password: form.password,
              }
            })
            .then((response) => {
                if(response.data.status == "success"){
                    window.location.href = '/api/station';
                }else{
                    console.log(response.data.msg);
                    this.$message.error(response.data.msg);
                }
            });
        },

    }
}).$mount('#app');


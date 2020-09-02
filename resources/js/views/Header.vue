<template>
  <el-container v-if="$route.path != '/login'">
    <el-header style="text-align: right; font-size: 20px; font-weight:bold;">
      <el-menu
            default-active="/checkin"
            class="el-menu-demo"
            mode="horizontal"
            @select="handleSelect"
            text-color="#0168b7"
            active-text-color="#0168b7">
        <el-menu-item style="background-color: transparent;" index="/checkin" route="/checkin">打卡</el-menu-item>
        <el-menu-item style="background-color: transparent;" index="/checklog" route="/checklog">出缺勤紀錄</el-menu-item>
        <el-menu-item style="background-color: transparent;" index="/todo" route="/todo">代辦事項</el-menu-item>
        <el-menu-item style="background-color: transparent;" index="/report" route="/report" v-if="show">人事報表</el-menu-item>
        <el-menu-item style="background-color: transparent;" index="/modifyAccount" route="/modifyAccount" v-if="show">人員管理</el-menu-item>
        <el-menu-item style="background-color: transparent;" index="/groupSetting" route="/groupSetting" v-if="show">部門設定</el-menu-item>
        <el-menu-item style="background-color: transparent;" index="/checkInSetting" route="/checkInSetting" v-if="setting">打卡設定</el-menu-item>
        <el-menu-item style="background-color: transparent;" index="/todayCheckIn" route="/todayCheckIn" v-if="show">簽到狀態</el-menu-item>
        <span> {{ getAccount }} </span>
        <el-avatar v-if="avatar === ''" class="avatar" style="word-break: break-all;"> {{ this.$store.state.account }} </el-avatar>
        <el-avatar v-else class="avatar" :size="50" :src="avatar" @error="errorHandler">
          <img src="https://cube.elemecdn.com/e/fd/0fc7d20532fdaf769a25683617711png.png"/>
        </el-avatar>
        <el-dropdown @command="handleCommand" trigger="hover" :show-timeout="50">
          <i class="el-icon-setting" style="margin-right: 5px; font-size: 22px; font-weight:bold;">
            <el-dropdown-menu slot="dropdown">
              <el-dropdown-item command="logout">登出</el-dropdown-item>
              <el-dropdown-item command="changePassword">更改密碼</el-dropdown-item>
              <el-dropdown-item command="changeAvatar">更換頭貼</el-dropdown-item>
            </el-dropdown-menu>
          </i>
        </el-dropdown>
        <span style="margin-right: 20px;">{{ date }}</span>
      </el-menu>
    </el-header>

    <el-dialog title="更改密码" :visible.sync="dialogFormVisible" @closed="resetForm">
      <el-form :model="form" label-width="140px">
        <el-form-item label="請輸入舊密碼">
          <el-input v-model="form.old_password" size="mini" show-password></el-input>
        </el-form-item>
        <el-form-item label="請輸入新密碼">
          <el-input v-model="form.new_password" size="mini" show-password></el-input>
        </el-form-item>
        <el-form-item label="再次確認新密碼">
          <el-input v-model="form.new_password2" size="mini" show-password></el-input>
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <!-- <el-button @click="dialogFormVisible = false">取 消</el-button> -->
        <el-button type="primary" @click="changePassword" style="border-radius: 10px; width: 100px;">確 定</el-button>
      </div>
    </el-dialog>

    <el-dialog title="更換頭貼" :visible.sync="avatarDialog" @closed="resetForm">
      <div style="text-align-last: center;">
        <el-avatar :size="100" :src="upload" style="margin-bottom: 10px;"></el-avatar><br />
        <label class="fileinput">
          <i class="el-icon-picture"></i> 上傳圖片
          <input id="fileimg" type="file" @change="getbas64" accept="image/*" style="display: none">
        </label>
      </div>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click="changeAvatar" style="border-radius: 10px; width: 100px; margin-top: 50px; font-size: 16px; font-weight: bold">
          確 定
        </el-button>
      </div>
    </el-dialog>
  </el-container>
</template>

<script>
import { mapState, mapGetters } from 'vuex'
let timeoutID = ''
  export default {
    data() {
      return {
        activeIndex: null,
        disable: true,
        code: '',
        show: false,
        setting: false,
        date: '',
        dialogFormVisible: false,
        avatarDialog: false,
        form: {
          old_password:'',
          new_password:'',
          new_password2:'',
        },
        size: 1,
        avatar: '',
        upload: ''
      }
    },
    created: function() {
      this.time();
    },
    beforeDestroy() {
      if (timeoutID) {
        clearInterval(timeoutID) //在vue实例销毁钱，清除我们的定时器
      }
    },
    mounted() {
      this.getAvatar()
      // console.log(this.$route.path);
      timeoutID = setInterval(() => {
        this.time()
      }, 1000)
    },
    methods: {
      handleSelect(s) {
        this.$router.push( { path: s} ).catch(err => {})
      },
      handleCommand(s) {
        if(s === "logout") {
          this.logout()
        } else if (s === 'changePassword') {
          this.dialogFormVisible = true
        } else if (s === 'changeAvatar') {
          this.upload = this.avatar
          this.avatarDialog = true
        }
      },
      resetForm() {
        this.form.old_password  = ''
        this.form.new_password  = ''
        this.form.new_password2 = ''
      },
      changePassword() {
        axios.post('api/changePassword', this.form)
        .then((res)=>{
          this.$message({
            type: 'success',
            message: res.data.data
          });
          this.dialogFormVisible = false
          this.resetForm()
          this.logout()
        })
        .catch((err)=>{
          this.resetForm()
          this.$message.error(err.response.data.msg)
        })
      },
      logout() {
        localStorage.removeItem('token');
        this.$router.replace({ path: "/login" })
        this.$store.commit('setAccount', {account: '', level: '', group: '' } )
      },
      time() {
        let timezone = 8; //目标时区时间，东八区
        let offset_GMT = new Date().getTimezoneOffset(); // 本地时间和格林威治的时间差，单位为分钟
        let nowDate = new Date().getTime(); // 本地时间距 1970 年 1 月 1 日午夜（GMT 时间）之间的毫秒数
        let d = new Date(nowDate + offset_GMT * 60 * 1000 + timezone * 60 * 60 * 1000);
        this.date = `${d.getFullYear()}/${`0${d.getMonth()+1}`.slice(-2)}/${`0${d.getDate()}`.slice(-2)} ${`0${d.getHours()}`.slice(-2)}:${`0${d.getMinutes()}`.slice(-2)}:${`0${d.getSeconds()}`.slice(-2)}`
      },
      getAvatar() {
        axios.get('/api/getAvatar')
        .then(res => {
          this.avatar = res.data.data
        })
      },
      changeAvatar() {
        if (this.upload.slice(0, 6) === 'images') {
          this.avatarDialog = false
          return false
        }
        axios.post('/api/changeAvatar', {pic: this.upload})
        .then(res => {
          this.getAvatar()
          this.$message.success(res.data.data)
          this.avatarDialog = false
        })
        .catch((err)=>{
          this.$message.error(err.response.data.msg)
        })
      },
      errorHandler() {
        return true
      },
      getbas64 () {
        let input = document.getElementById('fileimg')
        let fr = new FileReader()
        fr.onload = () => {
          this.imagedata = fr.result
          // document.getElementById('Image').innerHTML = `<el-avatar :size="45" src="${fr.result}"></el-avatar>`
          this.upload = fr.result
        }
        if (input.files[0]) {
          if (input.files[0].size / 1024 / 1024 < this.size) {
            fr.readAsDataURL(input.files[0])
          } else {
            this.$message({
              message: `檔案不能超過 ${this.size}MB`,
              type: 'warning',
              showClose: true
            })
          }
        }
      }
    },
    computed: {
      ...mapGetters([
        'getAccount',
      ]),
      ...mapState([
        'account',
      ])
    },
    watch: {
      '$store.getters.getLevel' (val) {
        if (val === 2)
          this.setting = true
        if (val === 1 || val === 2){
          this.show = true
        }
      },
      // '$store.state.level' (val) {
      //   // console.log(val, 1);
      //   if (val === 1 || val === 2){
      //     this.show = true
      //   }
      // },
      $route (to, from) {
        console.log(to.path, 2);
        this.activeIndex = to.path;
      }
    }
  }
</script>

<style scoped>
  .el-header {
    color: #808284;
    line-height: 60px;
  }

  .el-icon-setting {
    cursor: pointer;
    color: #0274ab;
  }

  /deep/.el-menu {
    background-color: transparent;
  }

  .el-menu.el-menu--horizontal {
    border-bottom: solid 0px;
  }

  /deep/.el-menu--popup-bottom-start {
    border-radius: 4px;
    font-size: 16px;
    font-weight:bold;
  }

  /deep/.el-menu-item {
    font-size: 20px;
    /* border-radius: 4px; */
  }

  .el-menu-item.is-active {
    border-bottom-width: 5px;
  }

  /deep/ .el-dialog {
    width: 530px;
    border-radius: 15px;
}

  /deep/ .el-dialog__header {
    background-color: #00b8ee;
    border-radius: 15px 15px 0 0;
    margin-bottom: 0px;
    text-align: center;
  }

  /deep/ .el-dialog__header .el-dialog__title {
    color: #fff;
    font-size: 20px;
    font-weight: bold;
  }

  /deep/ .el-dialog__body {
    padding: 30px 20px 0 20px
  }

  /deep/ .el-icon-close:before {
    color: #fff;
  }

  /deep/ .el-form-item__label {
    font-size: 18px;
    font-weight: bold;
    color: #9e9e9e;
    width: 200px;
  }

  /deep/ .el-input__inner {
    border: 1px solid #9e9e9e;
    width: 300px;
  }

  /deep/ .el-dialog__footer {
    text-align-last: center;
  }

  .avatar {
    position: relative;
    top: 10px;
  }

  .fileinput {
    margin-bottom: 10px;
    padding: 8px 8px;
    max-width: 90px;
    text-align: center;
    border: 1px solid #167Dff;
    border-radius: 24px;
    background-color: #167Dff;
    color: #FFF;
    cursor: pointer;
    font-size: 16px;
  }
</style>
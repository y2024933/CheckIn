<template>
  <div>
    <center>
      <el-button style="margin-top: 30px; font-weight:bold; border-radius: 12px; background-color: #4489ca; color: #FFF;" @click="dialogTableVisible2=true">
        <i class="el-icon-plus" style="font-weight:bold; font-size: x-large;"></i>
        <span style="font-size: 21px; float: right;">创建帐号</span>
      </el-button>
    	<el-table
    	  v-loading="loading"
    	  stripe
    	  :data="tableData"
    	  size="mini"
    	  style="width: 122vh; margin: 25px 0; padding: 20px 60px 40px 60px; font-size: 18px; border-radius: 12px; color: #bfbfbf; font-weight: bold;">
    	  <el-table-column
    	    label="帳號"
    	    width="140">
    	    <template slot-scope="scope">
    	      <span style="margin-left: 5px">{{ scope.row.accountcode }}</span>
    	    </template>
    	  </el-table-column>
    	  <el-table-column
    	    label="员工编码"
    	    width="180">
    	    <template slot-scope="scope">
    	      <span style="margin-left: 5px">{{ scope.row.nickname }}</span>
    	    </template>
    	  </el-table-column>
    	  <el-table-column
    	    label="权限"
    	    width="140"
          :filters="[{ text: '超级管理员', value: 2 }, { text: '管理', value: 1 }, { text: '普通', value: 0 }]"
          :filter-method="filterLevel">
    	    <template slot-scope="scope">
    	      <span style="margin-left: 5px">{{ (scope.row.level == '2') ? '超级管理员' : (scope.row.level == '1') ? '管理' : '普通' }}</span>
    	    </template>
    	  </el-table-column>
    	  <el-table-column
          label="部门"
          width="140"
          :filters="filter_group"
          :filter-method="filterGroup">
          <template slot-scope="scope">
            <span style="margin-left: 5px">{{ group_setting[scope.row.group] }}</span>
          </template>
        </el-table-column>
        <el-table-column
          label="组别"
          width="140"
          :filters="filter_class"
          :filter-method="filterClass">
          <template slot-scope="scope">
            <span style="margin-left: 5px">{{ class_group[scope.row.class] }}</span>
          </template>
          </el-table-column>
    	  <el-table-column
    	    label="班别"
    	    width="140"
          :filters="filter_shift"
          :filter-method="filterShift">
    	    <template slot-scope="scope">
    	      <span style="margin-left: 5px">{{ allShift[scope.row.shift] }}</span>
    	    </template>
    	  </el-table-column>
    	  <el-table-column label="">
    	    <template slot-scope="scope">
    	      <el-button
    	        size="mini"
    	        type="warning"
    	        @click="handleEdit(scope.$index, scope.row), dialogTableVisible=true"
              style="margin-right: 18px;">编辑</el-button>
    	      <el-button
    	        size="mini"
    	        type="warning"
    	        @click="handleDel(scope.$index, scope.row)"><i class="el-icon-delete-solid" style="color: #989898;"></i></el-button>
    	    </template>
    	  </el-table-column>
    	</el-table>
    </center>

    <el-dialog title="" :visible.sync="dialogTableVisible" center>
      <center>
        <el-form ref="form" :model="form" label-width="" size="small">
          <el-form-item label="">
            <span>员工编码</span>
            <el-input v-model="form.nickname"></el-input>
          </el-form-item>
          <el-form-item label="">
            <span>权限</span>
            <el-select v-model="form.editLevel" placeholder="请选择">
              <el-option value='2' label='超级管理员' :disabled="$store.state.level != 2"></el-option>
              <el-option value='1' label='管理'></el-option>
              <el-option value='0' label='普通'></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="">
            <span>部门</span>
            <el-select v-model="form.editGroup" placeholder="请选择">
              <el-option v-for="(val, key) in group_setting" :key="key" :label="val" :value="key"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="">
            <span>组别</span>
            <el-select v-model="form.editClass" placeholder="请选择">
              <el-option v-for="(val, key) in class_setting" :key="key" :label="val" :value="key"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="">
            <span>班别</span>
            <el-select v-model="form.editShift" placeholder="请选择">
              <el-option v-for="(shift, key) in allShift" :key="key" :label="shift" :value="key"></el-option>
            </el-select>
          </el-form-item>
          <el-button @click="edit" style="font-family: Microsoft JhengHei;margin-top: 10px; font-size: 18px; color: #FFF; background-color: #4489ca; border-radius: 10px; width: 120px;">送出</el-button>
        </el-form>
      </center>
    </el-dialog>

    <el-dialog title="" :visible.sync="dialogTableVisible2" center @close="resetForm2">
      <center>
        <el-form ref="form" :model="form" label-width="" size="small" style="">
          <el-form-item label="">
            <span>帐号</span>
            <el-input v-model="form2.account"></el-input>
          </el-form-item>
<!--           <el-form-item label="">
            <span>员工编码</span>
            <el-input v-model="form2.name"></el-input>
          </el-form-item> -->
          <el-form-item label="">
            <span>密码</span>
            <el-input v-model="form2.password" show-password></el-input>
          </el-form-item>
          <el-form-item label="">
            <span>确认密码</span>
            <el-input v-model="form2.confirm" show-password></el-input>
          </el-form-item>
          <el-form-item label="">
            <span>层级</span>
            <el-select v-model="form2.insert_level" placeholder="选择会员层级">
              <el-option label="普通" value="0"></el-option>
              <el-option label="管理" value="1"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="">
            <span>部门</span>
            <el-select v-model="form2.insert_group" placeholder="选择部门">
              <el-option v-for="(val, key) in group_setting" :key="key" :label="val" :value="key"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="">
            <span>组别</span>
            <el-select v-model="form2.insert_class" placeholder="选择部门">
              <el-option v-for="(val, key) in class_setting" :key="key" :label="val" :value="key"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="">
            <span>班别</span>
            <el-select v-model="form2.insert_shift" placeholder="选择班别">
              <el-option v-for="(val, key) in select_shift" :key="key" :label="val.title" :value="val.id"></el-option>
            </el-select>
          </el-form-item>
          <el-button @click="onSubmit" style="font-family: Microsoft JhengHei;margin-top: 10px; font-size: 18px; color: #FFF; background-color: #4489ca; border-radius: 10px; width: 120px;">立即创建</el-button>
        </el-form>
      </center>
    </el-dialog>
  </div>
</template>

<script>
  export default {
    data() {
      return {
      	tableData: [],
        loading: false,
        dialogTableVisible: false,
        dialogTableVisible2: false,
        form: {
          id: '',
          nickname: '',
          editLevel: '',
          editGroup: '',
          editClass: '',
          editShift: ''
        },
        allShift: [],
        form2: {
          account: '',
          password: '',
          confirm: '',
          name: '',
          insert_level: '',
          insert_group: '',
          insert_class: '',
          insert_shift: ''
        },
        select_shift: {},
        filter_shift: [],
        filter_group: [],
        filter_class: [],
        group_setting: [],
        all_class: [],
        class_group: [],
        class_setting: [],
      }
    },
    mounted() {
      this.getShift()
      this.loading = true
      this.getAccount()
    	this.getGroupSetting()
    },
    methods: {
      getAccount() {
        axios.get('api/getAliveAccount')
        .then((res) => {
          this.tableData = res.data.data
          this.loading = false
        })
      },
      getGroupSetting() {
        axios.post('api/getGroupSetting')
        .then((res) => {
          this.group_setting = res.data.data.group
          this.all_class     = res.data.data.class
          this.class_group   = res.data.data.class_group
          this.filter_group  = res.data.data.filter_group
          this.filter_class  = res.data.data.filter_class
        })
      },
    	handleEdit(index, row) {
        this.form.id        = row.id
        this.form.nickname  = row.nickname
        this.form.editGroup = String(row.group)
        this.form.editClass = String(row.class)
        this.form.editLevel = String(row.level)
        this.form.editShift = String(row.shift)
    	},
    	handleDel(index, row) {
    	  this.$confirm(`是否删除 ${row.accountcode} ?`, '提示', {
    	    confirmButtonText: '确定',
    	    cancelButtonText: '取消',
    	    type: 'warning'
    	  }).then(() => {
    	    axios.post('api/delAccount', {id: row.id})
    	    .then((res) => {
    	      if(res.data.code == 0) {
    	        this.$message({
    	          type: 'success',
    	          message: res.data.data
    	        });
    	        this.getAccount()
    	      }
    	    }).catch((err) => { this.$message.error(err.response.data.msg) })
    	  }).catch(() => {});
      },
      resetForm() {
        this.form.id        = ''
        this.form.nickname  = ''
        this.form.editGroup = ''
        this.form.editLevel = ''
        this.form.editClass = ''
        this.form.editShift = ''
      },
      getShift() {
        axios.post('api/getShiftSetting', {status:'1'})
        .then((res) => {
          for(let x in res.data.data) {
            this.filter_shift.push({
                                    text: res.data.data[x].title,
                                    value: res.data.data[x].id,
            });
          }
          this.select_shift = res.data.data
          this.allShift = res.data.data.reduce((obj, val) => {
            obj[val.id] = val.title
            return obj
          }, {})
        })
      },
      edit() {
        axios.post('api/editAccount', this.form)
        .then(res => { this.$message.success(res.data.data) })
        .catch(err => { this.$message.error(res.response.data.msg) })
        this.dialogTableVisible = false
        this.getAccount()
      },
      onSubmit() {
        if (this.form2.password !== this.form2.confirm) {
          this.$message.error('密码确认不相符')
          return false
        }
        axios.post('/api/register', this.form2)
          .then(res => {
            this.$message.success('创建成功')
            this.resetForm2()
            this.getAccount()
          })
          .catch(err => this.$message.error(err.response.data.msg))
      },
      resetForm2() {
        this.form2.account = ''
        this.form2.password = ''
        this.form2.confirm = ''
        this.form2.name = ''
        this.form2.insert_level = ''
        this.form2.insert_group = ''
        this.form2.insert_class = ''
        this.form2.insert_shift = ''
        this.dialogTableVisible2 = false
        this.class_setting = []
      },
      filterGroup(value, row) {
        return row.group === value;
      },
      filterClass(value, row) {
        return row.class === value;
      },
      filterLevel(value, row) {
        return row.level === value;
      },
      filterShift(value, row) {
        return row.shift === value;
      },
    },
    watch: {
      "form2.insert_group"(s) {
        this.class_setting = this.all_class[s]
      },
      "form.editGroup"(s) {
        this.class_setting = this.all_class[s]
      }
    }
  }
</script>

<style scoped>
  /deep/.el-input {
		width: 200px;
	}

  /deep/.el-dialog__headerbtn .el-dialog__close {
      font-size: x-large;
  }

  /deep/.el-table--striped .el-table__body tr.el-table__row--striped td {
      background: #f3f3f3;
  }

  /deep/.el-input--small .el-input__inner {
    width: 248px;
    height: 40px;
    border-color: #b6b6b6;
    border-width: 2px;
  }

  /deep/.el-form-item span {
    padding: 0;
    color: #b6b6b6;
    font-size: 17px;
    margin-right: 15px;
  }

  /deep/.el-dialog {
    width: 425px;
    border-radius: 14px;
    font-weight: bold;
  }

  /deep/.el-form-item__content {
    text-align: -webkit-right;
    margin-right: 80px;
  }

  /deep/.el-input__suffix-inner {
    margin-left: 100px;
  }

  /deep/.el-input .el-input--small .el-input--suffix {
    padding-right: 70px;
  }

  .el-button--warning {
    font-weight: bold;
    font-size: 18px;
    padding: 0px;
    color: #bfbfbf;
    background-color: transparent;
    border-color: transparent;
  }

  /deep/.el-table__column-filter-trigger i {
      font-size: 22px;
      margin-left: 5px;
      font-weight:bold;
  }

  .el-select-dropdown__item {
    font-size: 16px;
    color: #b6b6b6;
    font-weight:bold;
    font-family: Microsoft JhengHei;
    width: 246px;
  }

  /deep/.el-select .el-input .el-select__caret {
    font-size: 20px;
    font-weight: bold;
  }

  /deep/.el-input__inner {
    font-size: 15px;
    font-weight: bold;
    color: #b6b6b6;
  }
</style>
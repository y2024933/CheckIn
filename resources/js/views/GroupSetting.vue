<template>
  <center>
    <el-button @click="dialogTableVisible5=true" style="margin-top: 2%; font-weight:bold; border-radius: 12px; background-color: #4489ca; color: #FFF;">
      <span style="font-size: 21px; float: right;">新增部門</span>
    </el-button>
    <el-button @click="dialogTableVisible2=true" style="margin-top: 2%; font-weight:bold; border-radius: 12px; background-color: #4489ca; color: #FFF;">
      <span style="font-size: 21px; float: right;">組別管理</span>
    </el-button>
    <el-table
      v-loading="loading"
      :data="tableData1"
      size="mini"
      style="width: 50vh; margin-top: 2%; padding: 15px 30px 30px 30px; font-size: 18px; color: #b6b6b6;">
      <el-table-column
        label="部門"
        width="120"
        prop="title">
      </el-table-column>
      <el-table-column
        label="組別"
        width="160"
        prop="class_ch">
      </el-table-column>
      <el-table-column label="">
        <template slot-scope="scope">
          <el-button
            size="mini"
            type="warning"
            @click="handleEdit(scope.$index, scope.row, 'group'), dialogTableVisible1=true"
            style="margin-right: 18px;">編輯</el-button>
          <el-button
            size="mini"
            type="warning"
            @click="handleDel(scope.$index, scope.row, 'group')"><i class="el-icon-delete-solid" style="color: #989898;"></i></el-button>
        </template>
      </el-table-column>
    </el-table>

    <el-dialog title="部門" :visible.sync="dialogTableVisible1" center @close="resetForm2" style="margin-top: 12vh;">
      <el-form ref="form" :model="form1" label-width="" label-position="left">
        <el-form-item label="">
          <span style="font-size: 18px;">部門名稱</span>
          <el-input v-model="form1.group_title" style="width: 260px;"></el-input>
        </el-form-item>
      </el-form>
      <el-button @click="updateGroup()" style="font-weight: bold; font-size: 16px; border-radius: 12px; background-color: #4489ca; color: #FFF;">確定</el-button>
    </el-dialog>

    <el-dialog title="" :visible.sync="dialogTableVisible2" center @close="resetForm2" style="margin-top: 5vh;">
      <span class="select">部門</span>
      <el-select v-model="selectGroup" style="margin-right: 28px;">
        <el-option
          v-for="v in selectData2"
          :key="v.id"
          :label="v.title"
          :value="v.id">
        </el-option>
      </el-select>
      <el-button @click="dialogTableVisible3=true" style="font-weight: bold; font-size: 16px; border-radius: 12px; background-color: #4489ca; color: #FFF;">新增組別</el-button>
      <center>
        <el-table
          v-loading="loading"
          :data="tableData2"
          size="mini"
          style="width: 48vh; margin-top: 2%; padding: 15px 30px 0px 30px; font-size: 18px; color: #b6b6b6;">
          <el-table-column
            label="組別名稱"
            width="140"
            prop="title">
          </el-table-column>
          <el-table-column
            label="所屬部門"
            width="140"
            prop="groupName">
          </el-table-column>
          <el-table-column label="">
            <template slot-scope="scope">
              <el-button
                size="mini"
                type="warning"
                @click="handleEdit(scope.$index, scope.row, 'class'), dialogTableVisible4=true"
                style="margin-right: 18px;">編輯</el-button>
              <el-button
                size="mini"
                type="warning"
                @click="handleDel(scope.$index, scope.row, 'class')"><i class="el-icon-delete-solid" style="color: #989898;"></i></el-button>
            </template>
          </el-table-column>
        </el-table>
      </center>
    </el-dialog>

    <el-dialog title="" :visible.sync="dialogTableVisible3" center @close="resetForm2" style="margin-top: 11vh;">
      <el-form ref="form" :model="form2" label-width="" label-position="left">
        <el-form-item label="">
          <span style="font-size: 18px;">組別名稱</span>
          <el-input v-model="form2.class_title" style="width: 260px;"></el-input>
        </el-form-item>
        <el-form-item label="">
          <span style="font-size: 18px;">部門</span>
          <el-select v-model="form2.class_group" placeholder="">
            <el-option
              v-for="v in selectData1"
              :key="v.id"
              :label="v.title"
              :value="v.id">
            </el-option>
          </el-select>
        </el-form-item>
      </el-form>
      <el-button @click="insertClass()" style="font-weight: bold; font-size: 16px; border-radius: 12px; background-color: #4489ca; color: #FFF;">新增</el-button>
    </el-dialog>

    <el-dialog title="組別" :visible.sync="dialogTableVisible4" center @close="resetForm2" style="margin-top: 11vh;">
      <el-form ref="form" :model="form2" label-width="" label-position="left">
        <el-form-item label="">
          <span style="font-size: 18px;">組別名稱</span>
          <el-input v-model="form2.class_title" style="width: 260px;"></el-input>
        </el-form-item>
        <el-form-item label="">
          <span style="font-size: 18px;">部門</span>
          <el-select v-model="form2.class_group" placeholder="">
            <el-option
              v-for="v in selectData1"
              :key="v.id"
              :label="v.title"
              :value="v.id">
            </el-option>
          </el-select>
        </el-form-item>
      </el-form>
      <el-button @click="updateClass()" style="font-weight: bold; font-size: 16px; border-radius: 12px; background-color: #4489ca; color: #FFF;">確定</el-button>
    </el-dialog>

    <el-dialog title="部門" :visible.sync="dialogTableVisible5" center @close="resetForm2" style="margin-top: 12vh;">
      <el-form ref="form" :model="form1" label-width="" label-position="left">
        <el-form-item label="">
          <span style="font-size: 18px;">部門名稱</span>
          <el-input v-model="form1.group_title" style="width: 260px;"></el-input>
        </el-form-item>
      </el-form>
      <el-button @click="insertGroup()" style="font-weight: bold; font-size: 16px; border-radius: 12px; background-color: #4489ca; color: #FFF;">確定</el-button>
    </el-dialog>
  </center>
</template>

<script>
  export default {

    data() {
      return {
        tableData1: [],
        tableData2: [],
        tableData3: [],
        selectData1: [],
        selectData2: [],
        selectGroup: 0,
        form1: {
          group_id: '',
          group_title: '',
        },
        form2: {
          class_id: '',
          class_group: 0,
          class_title: '',
        },
        loading: false,
        dialogTableVisible1: false,
        dialogTableVisible2: false,
        dialogTableVisible3: false,
        dialogTableVisible4: false,
        dialogTableVisible5: false,
      }
    },
    mounted() {
      this.getGroup()
      this.getClass()
    },
    methods: {
      getGroup() {
        this.loading = true
        axios.get('api/getGroup')
        .then((res) => {
          this.tableData1 = res.data.data
          this.selectData1 = [{id: 0, title: '請選擇部門'}].concat(res.data.data)
          this.selectData2 = [{id: 0, title: '全部'}].concat(res.data.data)
          this.loading = false
        })
      },
      getClass() {
        this.loading = true
        axios.post('api/getClass', { type: this.selectGroup })
        .then((res) => {
          this.tableData2 = res.data.data
          this.loading = false
        })
      },
      insertGroup() {
        if(this.form1.group_title.length <= 0) {
          this.$message({
            type: 'error',
            message: '請填寫部門名稱'
          });
        } else {
          axios.post('api/insertGroup', this.form1)
          .then((res) => {
            if(res.data.code == 0) {
              this.$message({
                type: 'success',
                message: res.data.data
              });
              this.resetForm()
              this.dialogTableVisible5 = false
            }
          })
          .catch(err => this.$message.error(err.response.data.msg))
        }
      },
      updateGroup() {
        if(this.form1.group_title.length <= 0) {
          this.$message({
            type: 'error',
            message: '請填寫部門名稱'
          });
        } else {
          axios.post('api/updateGroup', this.form1)
          .then((res) => {
            if(res.data.code == 0) {
              this.$message({
                type: 'success',
                message: res.data.data
              });
              this.resetForm()
              this.dialogTableVisible1 = false
            }
          })
          .catch(err => this.$message.error(err.response.data.msg))
        }
      },
      insertClass() {
        if(this.form2.class_group == 0) {
          this.$message({
            type: 'error',
            message: '請選擇部門'
          });
        } else {
          axios.post('api/insertClass', this.form2)
          .then((res) => {
            if(res.data.code == 0) {
              this.$message({
                type: 'success',
                message: res.data.data
              });
              this.resetForm()
              this.dialogTableVisible3 = false
            }
          })
          .catch(err => this.$message.error(err.response.data.msg))
        }
      },
      updateClass() {
        axios.post('api/updateClass', this.form2)
        .then((res) => {
          if(res.data.code == 0) {
            this.$message({
              type: 'success',
              message: res.data.data
            });
            this.resetForm()
            this.dialogTableVisible4 = false
          }
        })
        .catch(err => this.$message.error(err.response.data.msg))
      },
      handleEdit(index, row, type) {
        if(type == 'class') {
          this.form2.class_id    = row.id
          this.form2.class_group = parseInt(row.parent)
          this.form2.class_title = row.title
        } else if(type == 'group') {
          this.form1.group_id    = row.id
          this.form1.group_title = row.title
        }
      },
      handleDel(index, row, type){
        var api = ''
        if(type == 'class') {
          //删組別
          api = 'api/delClass'
        } else {
          //删部門
          api = 'api/delGroup'
        }
        this.$confirm(`是否刪除 ${row.title} ?`, '提示', {
          confirmButtonText: '確定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          axios.post(api, { id: row.id })
          .then((res) => {
            if(res.data.code == 0) {
              this.$message({
                type: 'success',
                message: res.data.data
              });
              this.getGroup()
              this.getClass()
            }
          })
          .catch(err => this.$message.error(err.response.data.msg))
        }).catch(() => {});
      },
      resetForm() {
        this.getGroup()
        this.getClass()
        this.form1.group_id    = ''
        this.form1.group_title = ''
        this.form2.class_id    = ''
        this.form2.class_group = 0
        this.form2.class_title = ''
      },
      resetForm2() {
        this.form1.group_id    = ''
        this.form1.group_title = ''
        this.form2.class_id    = ''
        this.form2.class_group = 0
        this.form2.class_title = ''
        this.selectGroup       = 0
      },
    },
    watch: {
      selectGroup() {
        this.getClass()
      }
    }
  };
</script>

<style scoped>
  /deep/.el-table--striped .el-table__body tr.el-table__row--striped td {
      background: #f3f3f3;
  }

  .el-divider__text.is-left {
    left: 60px;
  }

  .el-divider__text {
    font-family: Microsoft JhengHei;
    font-weight: bold;
    background-color: #cbeaff;
    color: #b6b6b6;
    font-size: 24px;
  }

  .el-divider--horizontal {
    background-color: #b6b6b6;
    height: 3px;
    width: 40%;
    border-radius: 4px;
  }

  .el-divider {
    margin-top: 4%;
  }

  .el-button--warning {
    font-weight: bold;
    font-size: 18px;
    padding: 0px;
    color: #bfbfbf;
    background-color: transparent;
    border-color: transparent;
  }

  /deep/.el-table--striped .el-table__body tr.el-table__row--striped td {
      background: #f3f3f3;
  }

  /deep/ .el-table thead {
    font-family: Microsoft JhengHei;
    font-weight: bold;
    color: #b6b6b6;
    font-size: 20px;
  }

  /deep/ .el-table__body {
    font-family: Microsoft JhengHei;
    font-weight: bold;
    color: #b6b6b6;
    font-size: 16px;
  }

  .el-select-dropdown__item {
    font-family: Microsoft JhengHei;
    font-weight: bold;
    color: #b6b6b6;
    font-size: 16px;
  }

  /deep/.el-input__inner {
    font-family: Microsoft JhengHei;
    font-weight: bold;
    color: #b6b6b6;
    font-size: 16px;
  }

  .select {
    font-family: Microsoft JhengHei;
    font-weight: bold;
    color: #b6b6b6;
    font-size: 16px;
    margin-right: 6px;
  }

  /deep/.el-dialog {
    width: 510px;
    border-radius: 14px;
    font-weight: bold;
  }

  /deep/ .el-table {
    border-radius: 14px;
    font-weight: bold;
  }

  /deep/.el-dialog__body {
    text-align: center;
  }

  /deep/.el-dialog__title, /deep/.el-form-item__content{
    font-family: Microsoft JhengHei;
    font-weight: bold;
    color: #b6b6b6;
    font-size: 26px;
  }
</style>
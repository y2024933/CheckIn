<template>
  <center>
    <el-main class="row-bg">
      <span style="text-align: right; float: left; margin-left: 65px; font-size: 18px;">状态
        <el-select v-model="status" placeholder="请选择" style="width: 150px;margin-top: 30px; margin-left: 15px; border-color: #b6b6b6;">
          <el-option key="0" label="全部显示" value="0"></el-option>
          <el-option key="1" label="有效" value="1" ></el-option>
          <el-option key="2" label="已删除" value="2"></el-option>
        </el-select>
      </span>
      <el-button size="mini" style="margin-top: 30px; float: right; display: inline; margin-right: 65px; padding: 0px; border: 0px" @click="dialogTableVisible2 = true">
        <img src="images/plus-square.svg" style="width: 30px;"></img>
      </el-button>
      <el-table
        v-loading="loading"
        stripe
        :data="tableData"
        size="mini"
        style="width: 82vh; padding: 15px 30px 30px 30px; font-size: 18px; color: #b6b6b6;">
        <el-table-column
          label="班别"
          width="160">
          <template slot-scope="scope">
            <span style="margin-left: 5px">{{ scope.row.title }}</span>
          </template>
        </el-table-column>
        <el-table-column
          label="上班时间"
          width="160">
          <template slot-scope="scope">
            <span style="margin-left: 5px">{{ scope.row.starttime }}</span>
          </template>
        </el-table-column>
        <el-table-column
          label="下班时间"
          width="160">
          <template slot-scope="scope">
            <span style="margin-left: 5px">{{ scope.row.endtime }}</span>
          </template>
        </el-table-column>
        <el-table-column
          label="跨天"
          width="100">
          <template slot-scope="scope">
            <span style="margin-left: 5px">{{ (scope.row.crossday == '1') ? '是' : '否' }}</span>
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
      <el-dialog title="编辑" :visible.sync="dialogTableVisible" center @close="resetForm">
        <center>
          <el-form ref="form" :model="form" label-width="" label-position="left">
            <el-form-item label="">
              <span>班别</span>
              <el-input v-model="form.title" style="width: 345px;"></el-input>
            </el-form-item>
            <el-form-item label="">
              <span>时间</span>
              <el-time-picker
                v-model="form.time[0]"
                value-format="HH:mm:ss"
                placeholder="开始时间"
                style="width: 180p;">
              </el-time-picker>
              <div style="font-size: 17px; color: #b6b6b6; display: inline;">至</div>
              <el-time-picker
                v-model="form.time[1]"
                value-format="HH:mm:ss"
                placeholder="结束时间">
              </el-time-picker>
            </el-form-item>
            <el-form-item label="">
              <span>跨天</span>
              <el-select v-model="form.crossday" placeholder="请选择" style="width: 344px;">
                <el-option key="0" label="否" value="0"></el-option>
                <el-option key="1" label="是" value="1" ></el-option>
              </el-select>
            </el-form-item>
            <el-button @click="update" style="font-family: Microsoft JhengHei;margin-top: 10px; font-size: 18px; color: #FFF; background-color: #4489ca; border-radius: 10px; width: 90px;">送出</el-button>
          </el-form>
        </center>
      </el-dialog>
      <el-dialog title="新增" :visible.sync="dialogTableVisible2" center @close="resetForm2">
        <center>
          <el-form ref="insertForm" :model="insertForm" label-width="" label-position="left">
            <el-form-item label="">
              <span>班别</span>
              <el-input v-model="insertForm.title" style="width: 345px;"></el-input>
            </el-form-item>
            <el-form-item label="">
              <span>时间</span>
              <el-time-picker
                v-model="insertForm.time[0]"
                value-format="HH:mm:ss"
                placeholder="开始时间">
              </el-time-picker>
              <div style="font-size: 17px; color: #b6b6b6; display: inline;">至</div>
              <el-time-picker
                v-model="insertForm.time[1]"
                value-format="HH:mm:ss"
                placeholder="结束时间">
              </el-time-picker>
            </el-form-item>
            <el-form-item label="">
              <span>跨天</span>
              <el-select v-model="form.crossday" placeholder="请选择" style="width: 344px;">
                <el-option key="0" label="否" value="0"></el-option>
                <el-option key="1" label="是" value="1" ></el-option>
              </el-select>
            </el-form-item>
            <el-button @click="insert" style="font-family: Microsoft JhengHei;margin-top: 10px; font-size: 18px; color: #FFF; background-color: #4489ca; border-radius: 10px; width: 90px;">送出</el-button>
          </el-form>
        </center>
      </el-dialog>
    </el-main>
  </center>
</template>

<script>
  export default {

    data() {
      return {
        tableData: [],
        status: '1',
        loading: false,
        dialogTableVisible: false,
        dialogTableVisible2: false,
        form: {
          id: '',
          title: '',
          time: [],
          crossday: '0',
        },
        insertForm: {
          title: '',
          time: ["00:00:00", "23:59:59"],
        }
      }
    },
    mounted() {
      this.getShiftSetting()
    },
    methods: {
      getShiftSetting() {
        this.loading = true
        axios.post('api/getShiftSetting', {status: this.status})
        .then((res) => {
          this.tableData = res.data.data
          this.loading = false
        })
      },
      handleEdit(index, row) {
        this.form.id = row.id
        this.form.title = row.title
        this.form.time.push(row.starttime)
        this.form.time.push(row.endtime)
        this.form.crossday = row.crossday
      },
      handleDel(index, row) {
        this.$confirm(`是否删除 ${row.title} ?`, '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          axios.post('api/delShiftSetting', {id: row.id})
          .then((res) => {
            if(res.data.code == 0) {
              this.$message({
                type: 'success',
                message: res.data.data
              });
              this.getShiftSetting()
            }
          }).catch((err) => {
            this.$message.error(err.response.data.msg)
          })

        }).catch(() => {
        });
      },
      update() {
        axios.post('api/editShiftSetting', this.form)
        .then((res) => {
          if(res.data.code == 0) {
            this.$message({
              type: 'success',
              message: res.data.data
            });
            this.getShiftSetting()
            this.dialogTableVisible = false
          }
        })
      },
      insert() {
        axios.post('api/addShiftSetting', this.insertForm)
        .then((res) => {
          if(res.data.code == 0) {
            this.$message({
              type: 'success',
              message: res.data.data
            });
            this.getShiftSetting()
            this.dialogTableVisible2 = false
          }
        }).catch((err) => {
          this.$message.error(err.response.data.msg)
        })
      },
      resetForm() {
        this.form.id = ''
        this.form.title = ''
        this.form.time = []
        this.form.crossday = '0'
      },
      resetForm2() {
        this.insertForm.title = ''
        this.insertForm.time = ["00:00:00", "23:59:59"]
      },
    },
    watch: {
      status() {
        this.getShiftSetting()
      }
    }
  };
</script>

<style scoped>
  .row-bg {
    width: 800px;
    margin-top: 50px;
    margin-left: 40vh;
    margin-right: 40vh;
    padding: 10px;
    background-color: #FFF;
    font-weight:bold;
    border-radius: 12px;
    color: #b6b6b6;
  }

  .el-date-editor.el-input, .el-date-editor.el-input__inner {
    width: 160px;
  }

  /deep/.el-dialog .el-dialog--center {
    margin-top: 30vh;
  }

  /deep/.el-dialog {
    width: 480px;
    border-radius: 14px;
    font-weight: bold;
  }

  /deep/.el-form-item span {
    padding: 0;
    color: #b6b6b6;
    font-size: 17px;
    margin-right: 15px;
  }

  /deep/.el-table--striped .el-table__body tr.el-table__row--striped td {
      background: #f3f3f3;
  }

  .el-dialog__body {
    height: 22vh;
    overflow: auto;
  }

  /deep/.el-dialog__title {
      line-height: 24px;
      font-size: 24px;
      color: #b6b6b6;
  }

  /deep/.el-input__inner {
    font-size: 16px;
    font-weight: bold;
    color: #b6b6b6;
    border-width: 2px;
    font-family: Microsoft JhengHei;
  }

  .el-button--warning {
    font-weight: bold;
    font-size: 18px;
    padding: 0px;
    color: #bfbfbf;
    background-color: transparent;
    border-color: transparent;
  }

  .el-table--border::after, .el-table--group::after, .el-table::before {
    background-color: transparent;
  }

  .el-select-dropdown__item {
    font-size: 16px;
    color: #b6b6b6;
    font-weight:bold;
    width: 150px;
  }

  /deep/.el-select .el-input .el-select__caret {
    font-size: 20px;
    font-weight: bold;
  }
</style>
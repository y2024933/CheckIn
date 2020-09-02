<template>
  <el-main>
    <center>
      <div class="block">
       <el-date-picker
         v-model="date"
         type="date"
         placeholder="選擇日期"
         value-format="yyyy-MM-dd"
         @change="getMemberByDate">
       </el-date-picker>
     </div>

     <el-table
       v-loading="loading"
       stripe
       :data="tableData"
       size="mini"
       style="width: 83vh; margin: 30px 0; padding: 30px; font-size: 18px; border-radius: 12px; font-weight: bold; color: #bfbfbf;">
       <el-table-column
         label="帳號"
         width="130">
         <template slot-scope="scope">
           <span style="margin-left: 5px">{{ scope.row.acc }}</span>
         </template>
       </el-table-column>
       <el-table-column
         label="簽到"
         width="60">
         <template slot-scope="scope">
           <el-checkbox style="padding-left: 10px;" v-model="scope.row.checkin" @change="send(scope.$index, scope.row.id, scope.row.acc, scope.row.checkin, '1')"></el-checkbox>
         </template>
        </el-table-column>
        <el-table-column
          label="簽到備註"
          width="220"
          align="center">
          <template slot-scope="scope">
            <div style="display: inline-flex;">
              <el-input v-model="scope.row.remark_in"></el-input>
              <el-button icon="el-icon-edit" size="mini" @click="edit_remark(scope.row.id, scope.row.remark_in, '1', scope.row.acc)"></el-button>
            </div>
          </template>
        </el-table-column>
        <el-table-column
          label="簽退"
          width="60">
          <template slot-scope="scope">
           <el-checkbox style="padding-left: 10px;" v-model="scope.row.checkout" @change="send(scope.$index, scope.row.id, scope.row.acc, scope.row.checkout, '2')"></el-checkbox>
         </template>
        </el-table-column>
        <el-table-column
           label="簽退備註"
           width="220"
           align="center">
          <template slot-scope="scope" style="display: inline-flex;">
            <div style="display: inline-flex;">
              <el-input v-model="scope.row.remark_out"></el-input>
              <el-button icon="el-icon-edit" size="mini" @click="edit_remark(scope.row.id, scope.row.remark_out, '2', scope.row.acc)"></el-button>
            </div>
          </template>
        </el-table-column>
     </el-table>
    </center>
  </el-main>
</template>

<script>
  export default {
    data() {
      return {
        date: new Date().toISOString().slice(0,10),
        tableData: [],
        loading: false,
        type: '',
        remark_in: '',
        remark_out: '',
        form: {
          switch: false,
          acc: '',
        }
      }
    },
    mounted() {
      this.getMemberByDate()
    },
    methods: {
      getMemberByDate() {
        this.loading = true
        axios.post('api/getMemberByDate', {date: this.date})
        .then((res) => {
          this.loading = false
          this.tableData = res.data.data
        })
      },
      send(key, id, acc, checkin, type) {
        this.type = type
        this.$confirm(`是否更改 【${acc}】 ${this.date} 簽到狀態?`, '提示', {
          confirmButtonText: '確定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          axios.post('api/editAccountCheckin', {id: id, status: checkin, date: this.date, type: type})
          .then((res) => {
            this.$message({
              type: 'success',
              message: res.data.data
            });
          }).catch((err)=>{
            this.$message.error(err.response.data.msg)
            if(this.type == '1') {
              this.tableData[key].checkin = !checkin
            } else {
              this.tableData[key].checkout = !checkin
            }
          })
        }).catch(()=>{
          setTimeout(()=>{
            if(this.type == '1') {
              this.tableData[key].checkin = !checkin
            } else {
              this.tableData[key].checkout = !checkin
            }
          },0)

        })
      },
      edit_remark(id, remark, type, acc) {
        let msg = (type=='1') ? '簽到' : '簽退'
        this.$confirm(`是否更改 【${acc}】 ${this.date} ${msg}備註?`, '提示', {
          confirmButtonText: '確定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          axios.post('api/editRemark', {id: id, remark: remark, date: this.date, type: type})
          .then((res) => {
            this.$message({
              type: 'success',
              message: res.data.data
            });
          }).catch((err)=>{
            this.$message.error(err.response.data.msg)
          })
        }).catch((err)=>{
        })
      },
    }
  };
</script>

<style scoped>
  /deep/.el-input__inner {
    /* padding: 0px 40px; */
    height: 34px;
    font-size: 18px;
    color: #bfbfbf;
    font-family: Microsoft JhengHei;
    font-weight: bold;
  }

  /deep/.el-table--striped .el-table__body tr.el-table__row--striped td {
    background: #f3f3f3;
  }

  /deep/.el-checkbox__inner {
    border-radius: 4px;
  }

  /deep/.el-input__icon {
    line-height: 33px;
    font-size: 24px;
  }
</style>
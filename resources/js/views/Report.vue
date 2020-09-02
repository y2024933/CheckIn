<template>
  <el-main class="main">
    <center>
      <div class="" style="margin-top: 3%; margin-left: 4%;">
        <el-date-picker
          v-model="date"
          type="month"
          placeholder="選擇日期"
          value-format="yyyy-MM"
          @change="">
        </el-date-picker>
      </div>
      <div style="margin-top: 16px; margin-left: 4%;">
<!--         <el-button @click="change('all', false)" style="font-family: Microsoft JhengHei; font-size: 18px; color: #FFF; background-color: #4489ca; border-radius: 10px; width: 150px; ">取消所有標記</el-button> -->
        <el-button @click="exportReport" style="font-family: Microsoft JhengHei; font-size: 18px; color: #FFF; background-color: #4489ca; border-radius: 10px; width: 150px; " download>匯出Excel</el-button>
      </div>
    </center>
    <span class="item" v-loading="loading" element-loading-background="transparent">
      <el-card v-for="(val, key) in member" :key='key' class="box-card">
        <div slot="header" class="clearfix" style="text-align: center">
          <span>{{ key }}</span>
        </div>
        <div v-for="(v, k) in val" style="text-align: center">
          <!-- <el-checkbox v-model="v.check" @change="change(k, v.check)"> -->
          <el-button class="text" type="text" @click="chooseMember(k), dialogTableVisible = true">{{ v.name }}</el-button>
          <!-- </el-checkbox> -->
        </div>
      </el-card>
    </span>
    <el-dialog :title="`${acc} 打卡紀錄`" :visible.sync="dialogTableVisible" center>
      <el-table :data="memberRecord" align='center' :row-class-name="tableRowClassName">
        <el-table-column property="date" label="日期" width="120"></el-table-column>
        <el-table-column property="starttime" label="上班" width="120">
          <template slot-scope="scope">
            <span style="margin-left: 5px">{{ scope.row.starttime }}</span>
          </template>
        </el-table-column>
        <el-table-column property="endtime" label="下班" width="120">
          <template slot-scope="scope">
            <span style="margin-left: 5px">{{ scope.row.endtime }}</span>
          </template>
        </el-table-column>
        <el-table-column property="late1" label="遲到時數" width="120">
          <template slot-scope="scope">
            <span style="margin-left: 5px">{{ scope.row.late1 }}</span>
          </template>
        </el-table-column>
        <el-table-column property="late2" label="早退時數" width="120">
          <template slot-scope="scope">
            <span style="margin-left: 5px">{{ scope.row.late2 }}</span>
          </template>
        </el-table-column>
        <el-table-column property="total" label="實際工時" width="120">
          <template slot-scope="scope">
            <span style="margin-left: 5px">{{ scope.row.total }}</span>
          </template>
        </el-table-column>
        <el-table-column property="remark1" label="上班備註" width="140">
          <template slot-scope="scope">
            <span style="margin-left: 5px">{{ scope.row.remark1 }}</span>
          </template>
        </el-table-column>
        <el-table-column property="remark2" label="下班備註" width="140">
          <template slot-scope="scope">
            <span style="margin-left: 5px">{{ scope.row.remark2 }}</span>
          </template>
        </el-table-column>
      </el-table>
    </el-dialog>
  </el-main>
</template>

<script>
  export default {

    data() {
      return {
        record: {},
        member: {},
        allMember: {},
        memberRecord: {},
        date: new Date().toISOString().slice(0, 7),
        late: {},
        acc: '',
        dialogTableVisible: false,
        loading: false,
      }
    },
    mounted() {
      this.loading = true
      this.getMember()
      this.getRecord()
    },
    methods: {
      // change(acc, status) {
      //   if(acc == 'all') {
      //     this.$confirm(`是否取消所有標記?`, '提示', {
      //       confirmButtonText: '确定',
      //       cancelButtonText: '取消',
      //       type: 'warning'
      //     }).then(() => {
      //       axios.post('/api/updateCheckReport', {acc: acc, check: status})
      //       .then((res) => {
      //           this.getMember()
      //       })
      //     }).catch(()=>{
      //     })
      //   } else {
      //     axios.post('/api/updateCheckReport', {acc: acc, check: status})
      //     .then((res) => {
      //     })
      //   }
      // },
      tableRowClassName({row, rowIndex}) {
        if (row.late == true) {
          return 'error-row';
        }
        return '';
      },
      getRecord() {
        axios.post('/api/getRecord', { date: this.date })
        .then((res) => {
          this.record = {}
          for(let x in res.data.data) {
            this.record[x] = []
            for(let y in res.data.data[x]) {
              this.record[x].push({
                date: y,
                starttime: res.data.data[x][y][1],
                endtime: res.data.data[x][y][2],
                late: res.data.data[x][y]['late'],
                late1: res.data.data[x][y]['late1'],
                late2: res.data.data[x][y]['late2'],
                total: res.data.data[x][y]['total'],
                remark1: res.data.data[x][y]['remark1'],
                remark2: res.data.data[x][y]['remark2'],
              })
            }
          }
          this.loading = false
        })
      },
      getMember() {
        axios.post('/api/getMember', {date: this.date})
        .then((res) => {
          this.member = res.data.data
          for(let x in res.data.data) {
            for(let y in res.data.data[x]) {
              this.allMember[y] = res.data.data[x][y]
            }
          }
        })
      },
      chooseMember(s) {
        this.acc = ''
        if(this.record[s]){
          this.acc = this.allMember[s].name
          this.memberRecord = this.record[s]
        } else {
          this.memberRecord = [{
            date: '',
            starttime: '',
            endtime: '',
            late: false,
            late1: '',
            late2: '',
            total: '',
            remark1: '',
            remark2: '',
          }]
        }
      },
      exportReport() {
        axios.get('/api/exportReport', {
          params: {date: this.date},
          responseType: 'blob' //指定返回数据的格式为blob
        })
        .then(response => {
          console.log(response);//把response打出来，看下图
          let url = window.URL.createObjectURL(response.data);
          console.log(url)
          var a = document.createElement("a");
          document.body.appendChild(a);
          a.href = url;
          a.download = `${this.date}月人事報表.xlsx`;
          a.click();
          window.URL.revokeObjectURL(url);
        })
        .catch(err => {
          console.log(`接口調用失敗`);
          console.log(err);
        })
      }
    },
    watch: {
      date() {
        this.loading = true
        this.getMember()
        this.getRecord()
      }
    }
  };
</script>

<style scoped>
  /deep/.el-input__inner {
    height: 34px;
    font-size: 18px;
    color: #bfbfbf;
    font-family: Microsoft JhengHei;
    font-weight: bold;
  }

  /deep/.el-input__icon {
    line-height: 33px;
    font-size: 24px;
  }

  .text {
    font-size: 16px;
  }

  /deep/.el-dialog__body {
    height: 50vh;
    overflow: auto;
  }

  /deep/.item {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    height: 540px;
/*     margin: 50px 0 0 85px;
    display: grid;
    grid-template-columns: 250px 250px 250px 250px;
    grid-gap: 30px;
    color: #444; */
  }

  .clearfix {
    font-size: 30px;
    color: #b6b6b6;
    font-weight:bold;
    font-family: Microsoft JhengHei;
  }

  .text {
    font-size: 18px;
    color: #b6b6b6;
    font-weight:bold;
    font-family: Microsoft JhengHei;
  }

  /deep/.el-dialog--center {
    border-radius: 12px;
  }

  /deep/.el-dialog__title {
    font-size: 22px;
    color: #747474;
    font-weight:bold;
    font-family: Microsoft JhengHei;
  }

  .el-table {
    font-size: 15px;
    color: #747474;
    font-weight:bold;
    font-family: Microsoft JhengHei;
  }

  /deep/.el-checkbox__inner {
    border-radius: 4px;
  }

  /deep/.el-table td, /deep/.el-table th {
    padding: 4px;
  }

  /deep/.el-dialog--center {
    width: 122vh;
    padding-bottom: 20px;
  }

  /deep/.el-card__body {
    overflow: auto;
    height: 41vh;
  }

  /deep/.el-table .error-row {
    background: #ffbcbc9e;
  }

  .clearfix:before,
  .clearfix:after {
    display: table;
    content: "";
  }
  .clearfix:after {
    clear: both
  }

  /deep/.box-card {
    margin-bottom: 22px;
    font-weight:bold;
    border-radius: 12px;
    width: 275px;
    margin-left: 4%;
    margin-top: 2%;
  }
</style>
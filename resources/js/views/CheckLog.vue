<template>
  <el-main style="display: flex; justify-content: center; overflow: inherit;">
    <el-calendar :first-day-of-week=7 v-loading="loading" element-loading-background="transparent">
      <!-- 这里使用的是 2.5 slot 语法，对于新项目请使用 2.6 slot 语法-->
      <template
        slot="dateCell"
        slot-scope="{date, data}">
        <p :class="data.isSelected ? 'is-selected' : ''">
          <div class="datetitle">{{ data.day.split('-').slice(2).join('') }} {{ data.isSelected ? '✔️' : ''}}</div>
          <div style="float: right;">
            <div :class="(record[data.day] && record[data.day]['late'] == true) ? 'checkin' : ''">{{ record[data.day] ? `上班: ${record[data.day][1]}` : ''  }}</div>
            <div class="checkout">{{ record[data.day] ? `下班: ${record[data.day][2]}` : ''  }}</div>
          </div>
        </p>
      </template>
    </el-calendar>
  </el-main>
</template>

<script>
  export default {
    data() {
      return {
        record: {},
        loading: true,
        today: '',
        late: {},
        shift: {},
        date: new Date().toISOString().slice(0, 7)
      }
    },
    mounted() {
      this.time()
      this.getRecord()
      //簡體改繁體
      document.getElementsByClassName('el-button--plain')[0].innerText = '上個月'
      document.getElementsByClassName('el-button--plain')[2].innerText = '下個月'
    },
    methods: {
      getRecord() {
        axios.get('/api/getAccShift')
        .then((res) => {
          this.shift = res.data.data
        })
        axios.post('/api/getRecordLog', { date: this.date })
        .then((res) => {
          this.record = res.data.data
          this.loading = false
        })
      },
      time() {
        let timezone = 8; //目标时区时间，东八区
        let offset_GMT = new Date().getTimezoneOffset(); // 本地时间和格林威治的时间差，单位为分钟
        let nowDate = new Date().getTime(); // 本地时间距 1970 年 1 月 1 日午夜（GMT 时间）之间的毫秒数
        let d = new Date(nowDate + offset_GMT * 60 * 1000 + timezone * 60 * 60 * 1000);
        this.today = `${d.getFullYear()}-${`0${d.getMonth()+1}`.slice(-2)}-${`0${d.getDate()}`.slice(-2)}`
      },
    }
  };
</script>

<style scoped>
  /deep/ .el-main {
    display: flex;
    justify-content: center;
    overflow: inherit;
  }

  /deep/ .el-calendar{
    margin-top: 15px;
    width: 1200px;
    background-color: #ffffffe6;
  }

  /deep/ .el-calendar__header {
    background-color: #cbeaff;
    text-align: center;
  }

  /deep/ .el-calendar__title {
    color: #4285b3;
    text-align: center;
    font-size: 28px;
    font-weight: bold;
  }

  /deep/ .el-calendar-table .el-calendar-day{
    padding: 1px 8px 8px 8px;
    /* background-color: #f9fdff; */
  }

  /deep/ .el-calendar__body {
    border: 1px solid #4cccf4;
    /* margin-top: 20px; */
    padding: 0;
  }

  .datetitle {
    font-size: 20px;
  }

  .checkin {
    color: red;
    /* margin-left: 40%; */
  }

  .checkout {
    /* margin-left: 40%; */
    /* color: orangered; */
  }

  /deep/ .el-calendar-table tr td:first-child {
    border: 1px solid #4cccf4;
  }

  /deep/ .el-calendar-table {
    /* border: 1px solid #4cccf4; */
    border-collapse: collapse;
    background-color: rgba(249,253,255,0.3);
  }

  /deep/ .el-calendar-table th {
     border: 1px solid #4cccf4;
  }

  /deep/ .el-calendar-table td {
     border: 1px solid #4cccf4;
  }

  /deep/ .el-button--mini {
    color: #fff;
    background: #409eff;
    border-radius: 15px;
    font-size: 14px;
    font-weight: bold;
    font-family: unset;
  }

</style>
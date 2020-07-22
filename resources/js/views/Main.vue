<template>
  <el-container style="height: 500px; border: 1px solid #eee">
      <el-header style="text-align: right; font-size: 12px">
        <el-dropdown>
          <a href="#" >
            <i class="el-icon-setting" style="margin-right: 5px">
              <el-dropdown-menu slot="dropdown">
                <el-dropdown-item>登出</el-dropdown-item>
                <el-dropdown-item>更改密碼</el-dropdown-item>
              </el-dropdown-menu>
            </i>
          </a>
        </el-dropdown>
        <span>Paul</span>
      </el-header>

    <el-container>
      <el-aside width="200px" style="background-color: rgb(238, 241, 246)">
        <el-menu
              default-active="1"
              class="el-menu-vertical-demo"
              background-color="#545c64"
              text-color="#fff"
              active-text-color="#ffd04b">
          <el-menu-item index="1" >
            <i class="el-icon-map-location"></i>
            <span slot="title">打卡</span>
          </el-menu-item>
          <el-menu-item index="2" route="123213">
            <i class="el-icon-date"></i>
            <span slot="title">出缺勤紀錄</span>
          </el-menu-item>
          <el-menu-item index="3" :disabled="disable">
            <i class="el-icon-document"></i>
            <span slot="title">人事報表</span>
          </el-menu-item>
        </el-menu>
      </el-aside>

      <el-main>



      </el-main>
    </el-container>
  </el-container>
</template>

<style>
  .el-header {
    background-color: #B3C0D1;
    color: #333;
    line-height: 60px;
  }

  .el-aside {
    color: #333;
  }
</style>

<script>
  export default {
    data: function() {
      return {
        disable: true
      }
    },
  };
</script>
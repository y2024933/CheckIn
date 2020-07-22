<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="icon" sizes="192x192" href="/images/icon.png">
  <title>打卡系統</title>
  <link rel="stylesheet" type="text/css" href="/css/app.css">
  <!-- <script src="https://cdn.socket.io/socket.io-1.4.5.js"></script> -->
  <!-- <script src="//61.31.209.39:6001/socket.io/socket.io.js"></script> -->
</head>
<body>
    <style>[v-cloak]{display:none;}</style>
    <div id="app" v-cloak>​
      <eheader v-if="$route.path != '/login'"></eheader>
      <router-view></router-view>
    </div>
    <script src="/js/app.js"></script>
    <script src="/js/router.js"></script>
</body>
</html>

<style>
  body {
    background: url(/images/background.jpg);
    background-repeat: no-repeat;
    background-attachment: fixed;
    background-position: center;
    background-size: cover;
    font-family: Microsoft JhengHei;
  }
</style>
​
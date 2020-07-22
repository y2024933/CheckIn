<template>
	<div class="wrapper">
		<div v-if="selfCheck['1']" round id="checkin" style="cursor: auto;">
			<p style="margin-top: 25%;">已簽到{{ selfCheck['1'] }}</p>
		</div>
		<div v-else round @click="type('1')" id="checkin">
			<p>上班簽到</p>
		</div>
		<div v-if="selfCheck['2']" round id="checkout" style="cursor: auto;">
			<p style="margin-top: 25%;">已簽退{{ selfCheck['2'] }}</p>
		</div>
		<div v-else round @click="type('2')" id="checkout">
			<p>下班簽退</p>
		</div>
		<div style="margin: 20px 0;"></div>
		<div class="today">
			<div slot="header" class="clearfix">
				<span v-if="$store.state.level === 2" class="department">公司</span>
				<span v-else class="department">{{ $store.state.groupName }}部门</span>
				<div class="check" @click="dialogVisible = !dialogVisible">尚未签到名单</div>
				<div class="check" style="margin-top: 10px;" @click="dialogVisible2 = !dialogVisible2">值日生名单</div>
			</div>
			<el-dialog custom-class="check" title="今日尚未签到名单" :visible.sync="dialogVisible" width="30%" :before-close="handleClose">
				<div v-for="(user, id) in noCheck" :key="id" style="margin-top: 15px; display:flex; margin-left: 30%;">
					<el-avatar v-if="user.avatar == ''" class="avatar" :size="45"> {{ user.account }} </el-avatar>
					<el-avatar v-else class="avatar" :size="45" :src="user.avatar"></el-avatar>
					<span style="font-weight: bold; font-size: 18px;">{{ user.account }}</span>
				</div>
			</el-dialog>
			<el-dialog :visible.sync="confirmVisible" width="35%">
				<p style="font-weight: bold; font-size: 32px; margin: 0 20px 10px 25px; color: #949494">是否【{{ info }}】?</p>
				<img v-if="checkType === '1'" style="width: 110px; height: 160px;" src="images/checkin.jpg" />
				<img v-else style="width: 170px; height: 170px;" src="images/checkout.jpg" /><br/>
				<el-button round size="medium" style="margin: 10px 30px 10px 0; width: 30%;" @click="confirmVisible = false">取消</el-button>
				<el-button round size="medium" type="primary" style="width: 30%;" @click="send">确定</el-button>
			</el-dialog>
			<el-dialog custom-class="check" title="值日生名单" :visible.sync="dialogVisible2" width="30%">
				<div style="margin-left: 38%; font-weight: bold; font-size: 20px; text-align: left">
					<div>一  Catch、Raven</div>
					<div>二  Alex、Jasper</div>
					<div>三  Max、Wendy</div>
					<div>四  Paul、Chelsea</div>
					<div>五  Andy、Ts</div>
					<div>六  Kitty、Jason</div>
					<div>七  Chris、Bob</div>
				</div>
			</el-dialog>
		</div>

		<el-card class="box-card" style="width: 500px;"  body-style="padding-top: 0px;" shadow="always">
		  <div slot="header" class="cardClearfix">
		    <p>公布栏</p>
				<el-radio-group v-model="channel">
  			  <el-radio label="0">全部</el-radio>
  			  <el-radio label="1">IT</el-radio>
  			  <el-radio label="2">TS</el-radio>
  			  <el-radio label="3">CS</el-radio>
  			</el-radio-group>
				<div style="display: inline-flex;">
					<el-input v-model="addInput" placeholder="请输入内容" size="mini" @keyup.enter.native="add" style="margin: 10px 0 5px 0;"></el-input>
					<el-button slot="append" icon="el-icon-edit" size="mini" @click="add" style="margin: 10px 0 5px 10px;"></el-button>
				</div>
				
		    <!-- <el-button style="float: right; padding: 3px 0" type="text">新增公告</el-button>
				 -->
		  </div>
		  <div class="text item">
				<div v-for="(annList, annId) in announcement" :id="'channel.' + annId">
					<p style="text-align: left; font-weight: bold; font-size: 24px;"> {{ annList.name }}</p>
					<hr />
					<div v-for="ann in annList.announcement" style="display: block; line-height: 28px;">
						<span style="font-size: 16px;"> {{ ann.content }} </span>
						<span style="float: right; font-size:12px; text-decoration: underline;"> {{ ann.cdate }} </span>
						<span style="float: right; margin-right: 12px; font-weight: bold;"> {{ ann.accountcode }} </span>
					</div>
				</div>
		  </div>
		</el-card>
	</div class="wrapper">
</template>

<script>
export default {
	data() {
		return {
			info: '',
			noCheck: '',
			image: '',
			checkType: '',
			selfCheck: [],
			dialogVisible: false,
			dialogVisible2: false,
			confirmVisible: false,
			addInput: '',
			announcement: [],
			contents: [],
			channel: '0'
		}
	},
	mounted() {
		this.todayCheck()
		this.getAnnouncement()
		let jwt_token = localStorage.getItem('token')
		Echo.connector.options.auth.headers['Authorization'] = 'Bearer ' + jwt_token
    Echo.options.auth = {
        headers: {
            Authorization: 'Bearer ' + jwt_token,
        },
    },
		Echo.channel(`publicAnnouncement`)
    .listen('PodcastAnnouncement', (e) => {
			console.log(e);
			this.updateAnnouncement(e)
			// this.contents.push(`${e.account}: $2{e.content}`)
		})
		
		// Echo.channel(`Group.it`)
    // .listen('PrivateAnnouncement', (e) => {
		// 	console.log(e);
		// 	// this.contents.push(`${e.account}: ${e.content}`)
		// })

	},
	methods: {
		type(s) {
			this.checkType = s
			this.info = (s === '1') ? '签到' : '簽退'
			this.image = (s === '1') ? 'images/checkin.jpg' : 'images/checkout.jpg'
			this.confirmVisible = true
		},
		send() {
			this.getUserIP((ip) => {
				console.log(ip, 'ip');
				if (ip === 'stop') {
					this.$message.error('请先更改Chrome设定')
					return
				}
				axios.post('/api/checkIn', { type: this.checkType, ip: ip })
				.then((res) => {
					if (res.data.code === 0) {
						this.todayCheck()
						this.$message({
							message: res.data.data,
							type: 'success'
						});
					}
				})
				.catch((e) => {
					if (e.response.data.code === 1) {
						this.$message({
							message: e.response.data.msg,
							type: 'error'
						});
					}
				})
			})

			this.confirmVisible = false
		},
		todayCheck() {
			axios.get('/api/todayCheck')
				.then(res => {
					if (res.data.data.length === 0) {
						this.noCheck = '今日都已签到完毕！'
					}
					this.noCheck = res.data.data.all
					this.selfCheck = res.data.data.self
				})
		},
		getAnnouncement() {
			axios.get('/api/getAnnouncement')
			.then(res => {
				console.log(res.data, 999)
				this.announcement = res.data.data
			})
		},
		updateAnnouncement(e) {
			// this.$nextTick(() => {
			// 	let ch = document.getElementById(`channel.${e.channel}`)
			// 	let p = document.createElement('p')
			// 	p.innerText = e.content
			// 	console.log(ch);
			// 	console.log(p);
			// 	ch.appendChild(p)
			// })
			this.announcement[e.channel].announcement.push({accountcode: e.account, content: e.content, cdate: e.cdate})
		},
		add() {
			axios.post('/api/postAnnouncement', {content: this.addInput, channel: this.channel})
			.then(res => {
				this.addInput = ''
			})
		},
		handleClose(done) {
			done();
		},
		//拿內網ip, 須配合chrome的設定
		getUserIP(onNewIP) { //  onNewIp - your listener function for new IPs
			//compatibility for firefox and chrome
			let myPeerConnection = window.RTCPeerConnection || window.mozRTCPeerConnection || window.webkitRTCPeerConnection;
			let pc = new myPeerConnection({
					iceServers: []
				}),
				noop = function() {},
				localIPs = {},
				check = true,
				ipRegex = /([0-9]{1,3}(\.[0-9]{1,3}){3}|[a-f0-9]{1,4}(:[a-f0-9]{1,4}){7})/g,
				key;

			function iterateIP(ip) {
				if (!localIPs[ip]) {
					check = false
					localIPs[ip] = true;
				}
				onNewIP(ip)
			}
			//create a bogus data channel
			pc.createDataChannel("");
			// create offer and set local description
			pc.createOffer().then(function(sdp) {
				sdp.sdp.split('\n').forEach(function(line) {
					if (line.indexOf('candidate') !== -1) {
						line.match(ipRegex).forEach(iterateIP)
					}
				});
				pc.setLocalDescription(sdp, noop, noop);
			}).catch(function(reason) {
				// An error occurred, so handle the failure to connect
			});
			//listen for candidate events
			pc.onicecandidate = function(ice) {
				// console.log(ice.candidate.candidate);
				if (!ice || !ice.candidate || !ice.candidate.candidate || !ice.candidate.candidate.match(ipRegex)) {
					if (check !== false) {
						iterateIP('stop')
					}
				} else {
					ice.candidate.candidate.match(ipRegex).forEach(iterateIP);
				}
			};
		}
	},
	watch: {
    // '$store.getters.group' (val) {
		// 	console.log(val);
		// 	console.log(this.$store.getters.group);
		// },
		'$store.state.group' (val) {
			console.log(val);
			Echo.channel(`Group.${val}`)
    	.listen('PrivateAnnouncement', (e) => {
				console.log(e);
				this.updateAnnouncement(e)
				// this.contents.push(`${this.$store.state.groupName}-${e.account}: ${e.content}`)
			});
		},
	}
};
</script>

<style scoped>
.wrapper {
  display: flex;
  flex-direction: row;
  justify-content: center;
  text-align: center;
  height: 580px;
  align-items: flex-start;
	width: 90%;
  margin: 50px 0px 0px 240px;
}

#checkin {
	background-color: #1fbff1;
	width: 340px;
	height: 340px;
	border-radius: 99em;
	padding: 10px;
	box-shadow: 0px 3px 10px 26px #1fbff15c;
	/* position: relative;
	top: 75px;
	left: -320px; */
	cursor: pointer;
	align-self: flex-start;
}

#checkin p {
	color: #fceeee;
	font-size: 60px;
	font-weight: bolder;
	margin-top: 120px;
}

#checkout p {
	color: #fceeee;
	font-size: 32px;
	font-weight: bolder;
	margin-top: 80px;
}

#checkin :hover,
#checkout :hover {
	transition: all 0.8s linear;
	transform: scale(1.2);
}

#checkout {
	background-color: #ebaacc;
	width: 200px;
	height: 200px;
	border-radius: 99em;
	padding: 10px;
	box-shadow: 0px 3px 10px 10px #ebaacca8;
	position: relative;
	/* top: -55px; */
	left: 100px;
	cursor: pointer;
	align-self: center;
}

.check {
	font-weight: bold;
	width: 120px;
	padding: 10px;
	font-size: 17px;
	color: #fceeee;
	border-radius: 20px;
	background-color: #0075aa;
	cursor: pointer;
}

.department {
	font-size: 50px;
	color: #0176a9;
	font-weight: bold;
}

.today,
.el-button {
	font-size: 16px;
}

.item {
	margin-bottom: 18px;
}

.clearfix {
  display: inline-block;
	/* position: relative;
	top: -480px;
	left: 280px; */
}

/* .clearfix:before,
.clearfix:after {
	display: table;
	content: "";
}

.clearfix:after {
	clear: both;
} */

.today {
	width: 480px;
}

/deep/ .el-dialog {
	border-radius: 15px;
}

/deep/ .check .el-dialog__header {
	background-color: #00b8ee;
	border-radius: 15px 15px 0 0;
	margin-bottom: 0px;
}

/deep/ .check .el-dialog__header .el-dialog__title {
	color: #fff;
	font-size: 22px;
	font-weight: bold;
}

/deep/ .check .el-dialog__body {
	padding: 20px 20px
}

/deep/ .el-icon-close:before {
	color: #fff;
}

.avatar {
	position: relative;
	top: -10px;
	left: -10px;
}

.text {
  font-size: 14px;
}
.item {
  margin-bottom: 18px;
}
.cardClearfix:before,
.cardClearfix:after {
  display: table;
  content: "";
}
.cardClearfix:after {
  clear: both
}
.box-card {
  width: 480px;
}
</style>
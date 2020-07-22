<template>
	<el-main>
		<div style="font-size: 38px; color: #ffffff; font-weight: bold;">打卡系統</div>
		<el-tabs type="card">
			<center>
				<img style="width: 450px; heigth: auto;" src="images/logo.png">
				<el-card class="box-card" style="padding: 10px; height: 300px; width: 550px; margin-top: 50px; border-radius:20px;">
					<p style="font-size: 30px; color: #00a0e9">登入</p>
					<el-form ref="form" :model="form">
						<el-row>
							<el-col :span="4"></el-col>
							<el-col :span="20">
								<el-input placeholder="帳號" v-model="form.accountcode" @keyup.enter.native="send" style="padding-left: 70px"></el-input>
							</el-col>
						</el-row>
						<el-row>
							<el-col :span="4"></el-col>
							<el-col :span="20">
								<el-input placeholder="密碼" v-model="form.password" style="margin-top: 20px; padding-left: 70px" @keyup.enter.native="send" show-password></el-input>
							</el-col>
						</el-row>
						<div>
							<el-button type="primary" style="margin-top: 20px; border-radius:10px;" @click="send">確定</el-button>
						</div>
					</el-form>
				</el-card>
			</center>
		</el-tabs>
	</el-main>
</template>

<script>
export default {
	data() {
		return {
			activeName: 'first',
			form: {
				accountcode: '',
				password: ''
			}
		};
	},
	methods: {
		send() {
			axios.post('/api/login', this.form)
				.then(res => {
					if (res.data.code === 0) {
						this.$message({
						  type: 'success',
						  message: '登入成功',
						  duration: 1500,
						  showClose: true,
						});
						localStorage.setItem('token', res.data.data);
						this.$router.replace({ path: "/checkIn" });
					}
				})
				.catch(err => this.$message.error(err.response.data.msg))
		},
	}
}
</script>

<style scoped>
	.el-input {
		width: 370px;
	}
</style>


<template>
	<center>
		<el-card class="box-card" style="margin-top: 100px; padding: 20px;">
			<el-form ref="form" :model="form" label-width="60px" size="small">
				<el-form-item label="帳號">
					<el-input v-model="form.account"></el-input>
				</el-form-item>
				<el-form-item label="名稱">
					<el-input v-model="form.name"></el-input>
				</el-form-item>
				<el-form-item label="密碼">
					<el-input v-model="form.password" show-password></el-input>
				</el-form-item>
				<el-form-item label="確認密碼">
					<el-input v-model="form.confirm" show-password></el-input>
				</el-form-item>
				<el-form-item label="層級">
					<el-select v-model="form.insert_level" placeholder="請選擇會員層級">
						<el-option label="普通" value="0"></el-option>
						<el-option label="管理" value="1"></el-option>
					</el-select>
				</el-form-item>
				<el-form-item label="部門">
					<el-select v-model="form.insert_group" placeholder="選擇部門">
						<el-option label="IT" value="IT"></el-option>
						<el-option label="CS" value="CS"></el-option>
						<el-option label="TS" value="TS"></el-option>
					</el-select>
				</el-form-item>
				<el-form-item label="班别">
					<el-select v-model="form.insert_shift" placeholder="選擇班别">
						<el-option v-for="(val, key) in select_shift" :key="key" :label="val.title" :value="val.id"></el-option>
					</el-select>
				</el-form-item>
				<el-form-item>
					<el-button type="primary" @click="onSubmit" style="margin-top: 20px;">立即創建</el-button>
				</el-form-item>
			</el-form>
		</el-card>
	</center>
</template>

<script>
export default {
	data() {
		return {
			form: {
				account: '',
				password: '',
				confirm: '',
				name: '',
				insert_level: '',
				insert_group: '',
				insert_shift: ''
			},
			select_shift: {}
		}
	},
	mounted() {
		this.getShiftSetting()
	},
	methods: {
		onSubmit() {
			if (this.form.password !== this.form.confirm) {
				this.$message.error('密碼確認不相符')
				return false
			}
			axios.post('/api/register', this.form)
				.then(res => {
					this.$message.success('創建成功')
					this.form.account = ''
					this.form.password = ''
					this.form.confirm = ''
					this.form.name = ''
					this.form.insert_level = ''
					this.form.insert_group = ''
					this.form.insert_shift = ''
				})
				.catch(err => this.$message.error(err.response.data.msg))
		},
		getShiftSetting() {
			axios.post('api/getShiftSetting', { status: '1' })
				.then((res) => {
					this.select_shift = res.data.data
				})
		},
	}
}
</script>


<style scoped>
	/deep/.el-input--small .el-input__inner {
		width: 200px;
	}

	/deep/.el-form-item__label {
		padding: 0;
	}
	.box-card {
    width: 480px;
  }
</style>
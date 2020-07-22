<template>
	<div>
		<!-- <button type="button" class="el-btn-primary"v-on:click="download">导出PDF</button> -->
		<div class="wrapper" id="resumeId">
			<div class="box" v-for="(data, account) in todoList" :key="account">
				<div style="display: block; font-weight: bold; font-size: 32px; margin-left: 5px;">
					<el-avatar v-if="data.avatar === ''" class="avatar" style="word-break: break-all;"> {{ account }} </el-avatar>
					<el-avatar v-else class="avatar" :size="45" :src="data.avatar" @error="errorHandler">
						<img src="https://cube.elemecdn.com/e/fd/0fc7d20532fdaf769a25683617711png.png" />
					</el-avatar>
					{{ account }}
					<img v-if="$store.state.account === account" src="images/brands.svg" style="width: 18px; margin-left: 5px;" class="self"></img>
				</div>
				<div style="display: inline-flex;" v-if="$store.state.account === account">
					<el-input v-model="addInput" placeholder="新增事项" size="mini" @keyup.enter.native="add" style="margin: 10px 0 5px 0;"></el-input>
					<el-button slot="append" icon="el-icon-edit" size="mini" @click="add" style="margin: 10px 0 5px 10px;"></el-button>
				</div>
				<hr/>
				<div v-for="(contents, index) in data.list" :key="index" class="content">
					<el-checkbox width="50%" v-model="contents.status" true-label="2" false-label="1" @change="change(index, contents.status, account)">{{ contents.content }}</el-checkbox>
					<i class="el-icon-delete-solid" @click="del(index, account)" style="font-size: 16px; cursor:pointer;"></i>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
export default {
	data() {
		return {
			addInput: '',
			todoList: {}
		}
	},
	mounted() {
		this.getList()
	},
	methods: {
		getList() {
			axios.get("/api/getList")
				.then(res => {
					this.todoList = res.data.data
				})
		},
		change(id, status, account) {
			if (account !== this.$store.state.account) {
				this.$message.error('无法更改他人状态')
				setTimeout(() => {
					this.$set(this.todoList[account]['list'][id], 'status', status === '1' ? '2' : '1')
				}, 0);
			} else {
				axios.post("/api/updateList", { 'id': id, 'status': status })
					.then((res) => {
						this.$message.success(res.data.data)
					}).catch((err) => {
						this.$message.error(err.response.data.msg)
					});
			}
		},
		add() {
			if (this.addInput === '') {
				this.$message.error('请输入新增事项')
				return false
			}
			axios.post("/api/writeList", { content: this.addInput })
				.then((res) => {
					this.addInput = ''
					this.$message.success(res.data.data)
					this.getList()
				})
				.catch((err) => { this.$message.error(err.response.data.msg) })
		},
		del(id, account) {
			if (account !== this.$store.state.account) {
				this.$message.error('无法删除他人事项')
				return false
			}
			this.$confirm('确定要删除纪录吗？', {
				confirmButtonText: '确定',
				cancelButtonText: '取消',
				type: 'warning'
			}).then(() => {
				axios.post('/api/delList', { id: id })
					.then((res) => {
						this.$message.success(res.data.data)
						this.getList()
					})
					.catch((err) => { this.$message.error(err.response.data.msg) })
			}).catch(() => {
				this.$message({ type: 'info', message: '已取消删除' });
			});
		},
		download() {
			this.getPdf('resumeId', 'todo')
		},
		errorHandler() {
			return true
		}
	},
}
</script>

<style scoped>
.wrapper {
	margin: 40px 0 0 40px;
	display: grid;
	grid-template-columns: 340px 340px 340px 340px 340px;
	/* grid-template-rows: 300px 300px 300px; */
	grid-gap: 20px;
	color: #444;
}

.box {
	background-color: #ffffff;
	color: #959595;
	height: 230px;
	border-radius: 5px;
	padding: 10px;
	word-break: break-all;
	overflow-y: auto;
}

.add {
	font-weight: bold;
	font-size: 15px;
	color: #006acd;
	margin-left: 15px;
	cursor: pointer;
}

/deep/ .el-checkbox {
	color: #959595;
	display: flex;
	margin-bottom: 10px;
}

/deep/.el-checkbox__label {
	white-space: pre-wrap;
	width: 250px;
	font-size: 15px;
	font-weight: bold;
}

.content {
	display: flex;
	align-items: flex-start;
}

/deep/ .el-icon-delete-solid:before {
	font-size: 18px;
}

.self:hover path {
	fill: #de5b78;
}

.avatar {
  position: relative;
  top: 6px;
}

::-webkit-scrollbar {
	width: 16px;
	height: 16px;
	background-color: #F5F5F5;
}

/*定义滚动条轨道
 内阴影+圆角*/

::-webkit-scrollbar-track {
	-webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
	border-radius: 10px;
	background-color: #F5F5F5;
}

/*定义滑块
 内阴影+圆角*/

::-webkit-scrollbar-thumb {
	border-radius: 10px;
	-webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, .3);
	background-color: #0073cd66;
}
</style>
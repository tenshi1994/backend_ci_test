Vue.component('Comment', {
    template: '#comment-template',
    props: ['comment', 'id', 'assign_id', 'likes'],
    name: 'Comment',
    data() {
        return {
        	replyOpen: false,
            text: '',
			likesCount: this.likes.length
        };
    },
    methods: {
        toggleReplyBox () {
            this.replyOpen = !this.replyOpen;
        },
        async reply() {
        	var self = this;
            axios
                .post('/main_page/comment', {
                    post_id: self.assign_id,
                    parent_id: self.id,
                	text: self.text
            	}).then(function (response) {
                	self.text = '';
					if(response.data.status == 'error') {
                        self.$root.showError(response.data.error_message)
						return;
					}

					self.$root.setPost(response.data.post);

            	});
        },
		async like() {
            var self = this;
            axios
                .post('/main_page/like', {
                    source: 'comment',
                    id: this.id,
                }).then(function (response) {
					if(response.data.status == 'error') {
						self.$root.showError(response.data.error_message)
						return;
					}
                    self.likesCount = response.data.likes.length
                    self.$root.$emit('update-user-likes', response.data.user_likes)

            });
		}
    }
});

Vue.component('User', {
	template: '#user-template',
	name: 'User',
	data() {
		return {
			isLogged: false,
			user: false,
			balanceOperations: false
		};
	},
	created(){
		var self = this;
		axios
			.get('/main_page/check_login')
			.then(function (response) {
				if(response.data.status != 'error') {
					self.isLogged = true
					self.user = response.data.user
				}
			})
	},
	methods: {
		balanceOperationsToggle: function() {
			var self = this
			axios
				.get('/main_page/get_balance_operations')
				.then(function (response) {
                    if(response.data.status == 'error') {
                        self.$root.showError(response.data.error_message)
                        return;
                    }

					self.balanceOperations = response.data.balance_operations;
					setTimeout(function () {
						$('#balanceOperationsModal').modal('show');
					}, 500);
				})
		},
	},
	mounted() {
		this.$root.$on('update-user-likes', likes => this.user.likes = likes);
		this.$root.$on('update-user-balance', balance => this.user.wallet_balance = balance);
	},
});

var app = new Vue({
	el: '#app',
	data: {
		login: '',
		pass: '',
		post: false,
		invalidLogin: false,
		invalidPass: false,
		invalidRefillAmount: false,
		invalidRefillErrorMsg: '',
		posts: [],
		amount: 0,
		boosterpackLikes: 0,
		postLikes: 0,
		commentText: '',
        packs: [],
        errorMsgs: {
            'need_auth' : 'You need to login to do this operation.',
            'wrong_params' : 'Something went wrong, please try again later.',
            'try_later' : 'Something went wrong, please try again later.',
            'no_data' : 'This action is now unavailable',
            'not_enough_balance' : 'You dont have enough balance to do this action, please refill balance',
            'not_enough_like_balance' : 'You dont have enough likes for this action, please refill likes'
        },
        errorMsg: ''
	},
	created(){
		var self = this
		axios
			.get('/main_page/get_all_posts')
			.then(function (response) {
				self.posts = response.data.posts;
			})

        axios
            .get('/main_page/get_all_boosterpacks')
            .then(function (response) {
                self.packs = response.data.boosterpacks;
            })
	},
	methods: {
		logout: function () {
			console.log ('logout');
		},
		logIn: function () {
			var self= this;
			if(self.login === ''){
				self.invalidLogin = true
			}
			else if(self.pass === ''){
				self.invalidLogin = false
				self.invalidPass = true
			}
			else{
				self.invalidLogin = false
				self.invalidPass = false
				axios.post('/main_page/login', {
					login: self.login,
					password: self.pass
				})
				.then(function (response) {
					location.reload();
				})
			}
		},
        refillBalance: function () {
			var self= this;
			if(self.amount === 0){
				self.invalidRefillAmount = true
			}
			else{
				self.invalidRefillAmount = false
				axios.post('/main_page/add_money', {
                    amount: self.amount,
				})
					.then(function (response) {
                        self.amount = 0;
						if(response.data.status == 'error') {
							self.showError(response.data.error_message)
						} else {
                            self.$root.$emit('update-user-balance', response.data.balance)
                        }

                        setTimeout(function () {
                            $('#refillModal').modal('hide');
                        }, 500);
					})
			}
		},
		openPost: function (id) {
			var self= this;
			axios
				.get('/main_page/get_post/' + id)
				.then(function (response) {
					self.setPost(response.data.post)
					if(self.post){
                        if(response.data.status == 'error') {
                            self.showError(response.data.error_message)
                        } else {
                            self.postLikes = self.post.likes.length;
                        }

                        setTimeout(function () {
                            $('#postModal').modal('show');
                        }, 500);
					}
				})
		},
		addLike: function (id) {
			var self= this;
			axios
				.post('/main_page/like', {
					source: 'post',
					id: id
				})
				.then(function (response) {
                    if(response.data.status == 'error') {
                        self.showError(response.data.error_message)
						return;
                    }

					self.postLikes = response.data.likes.length;
					self.$root.$emit('update-user-likes', response.data.user_likes)
				})

		},
		addComment: function() {
            var self = this;
            axios
                .post('/main_page/comment', {
                    post_id: self.post.id,
                    parent_id: 0,
                    text: self.commentText
                }).then(function (response) {
                self.commentText = '';
                if(response.data.status == 'error') {
                    self.showError(response.data.error_message)
                    return;
                }

                self.setPost(response.data.post);
            });
		},
		buyPack: function (id) {
			var self= this;
			axios
				.post('/main_page/buy_boosterpack', {
					id: id,
				})
				.then(function (response) {
                    if(response.data.status == 'error') {
                        self.showError(response.data.error_message)
                        return;
                    } else {
                        self.boosterpackLikes = response.data.likes
                        self.$root.$emit('update-user-likes', response.data.user_likes)
                        self.$root.$emit('update-user-balance', response.data.balance)
					}

					setTimeout(function () {
						$('#boosterpackModal').modal('show');
					}, 500);
				})
		},
        setPost: function (post) {
            this.post = post;
        },
		showError: async function(msg) {
			var self = this
            self.errorMsg = self.errorMsgs.hasOwnProperty(msg) ? this.errorMsgs[msg] : msg;

            $('#error-block').show()

            setTimeout(function () {
                $('#error-block').hide()
            }, 3000)
        },
	}
});


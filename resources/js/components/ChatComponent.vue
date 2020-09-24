<template>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header h3">Messages</div>

                <div class="card-body">
                    <ul class="list-unstyled" style="height: 300px; overflow-y: scroll" id="parent" v-chat-scroll>
                        <li v-for="message of messages" :key="message.id">
                            <b>{{message.user.name}}</b>
                            <p>{{message.message}}</p>
                        </li>
                    </ul>

                    <input @keydown="sendTypingEvent" @keyup.enter="sendMessage" v-model="message" type="text" name="message" class="form-control form-inline" placeholder="type messages..">
                    <button @click="sendMessage" class="btn btn-success btn-sm mt-2">Send</button>
                </div>
                <span v-if="activeUser">{{activeUser.name}} is Typing...</span>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header h3">Online</div>

                <div class="card-body">
                    <ul><li v-for="user of users">{{user.name}}</li></ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "ChatComponent.vue",
        props:['user'],
        data(){
            return{
                message: '',
                messages: {},
                users: {},
                activeUser: false,
                clearTimer: false,
            }
        },

        mounted() {
            this.getMessage();

            Echo.join('message-send')
                .here(user=>{
                    this.users = user;
                    console.log('here');
                })
                .joining(user=>{
                    this.users.push(user);
                    console.log('joining');
                })
                .leaving(user=>{
                    this.users = this.users.filter(u=>u.id !== user.id);
                    console.log('leaving');
                })
                .listen('MessageSend',(event)=>{
                    this.messages.push(event.chat);
                })
                .listenForWhisper('typing',user=>{
                    this.activeUser = user;

                    if(this.clearTimer){
                        clearTimeout(this.clearTimer);
                    }

                    this.clearTimer = setTimeout(()=>{
                        this.activeUser = false;
                    },3000)
                });

        },

        methods:{

            getMessage(){
                axios.get('/messages')
                    .then(response=>{
                        this.messages = response.data;
                        console.log('success');
                    })
                    .catch(error=>{
                        console.log(error);
                    })
            },

            sendMessage(){
                axios.post('/messages',{'message':this.message})
                    .then(response=>{
                        this.messages.push(response.data);
                    })
                    .catch(error=>{
                        console.log(error);
                    })
            },

            sendTypingEvent(){
                Echo.join('message-send')
                    .whisper('typing',this.user)
            }

        },
    }
</script>

<style scoped>

</style>

<template>
  <div id="app">

    <div class="border ">
      <b-navbar toggleable="lg" class="navbar-dark" type="" variant="primary">

        <b-navbar-toggle target="nav-collapse"></b-navbar-toggle>

        <b-collapse id="nav-collapse" type="bg-ligt" is-nav>

          <b-navbar-nav>
              <li class="nav-item active">
                <a class="nav-link"><b-nav-item to="/" exact-active-class="active" >Home</b-nav-item><span class="sr-only">(current)</span></a>
              </li>
              <li class="nav-item">
                <a class="nav-link"><b-nav-item to="/about" exact-active-class="active">About</b-nav-item></a>
              </li>
              <li class="nav-item">
                <a class="nav-link"><b-nav-item to="/contact" exact-active-class="active">Contact</b-nav-item></a>
              </li>
          </b-navbar-nav>

          <b-navbar-nav class="ml-auto">
              <b-nav-item-dropdown right>
                <template #button-content>
                  <em>Profile</em>
                </template>
                <b-dropdown-item v-show="loggedIn" @click="logout">Sign Out</b-dropdown-item>
                <b-dropdown-item v-b-modal="'login-modal'" v-show="!loggedIn">Sign In</b-dropdown-item>
                <b-dropdown-item v-b-modal="'changePassword-modal'" v-show="loggedIn">Change Password</b-dropdown-item>
                <b-dropdown-item v-b-modal="'delete-modal'" v-show="loggedIn">Delete Account</b-dropdown-item>
                <b-dropdown-item v-b-modal="'register-modal'" v-show="!loggedIn">Register</b-dropdown-item>
                <b-dropdown-item b-nav-item to="/publish" v-show="loggedIn">Publish New Article</b-dropdown-item>
              </b-nav-item-dropdown>
          </b-navbar-nav>

        </b-collapse>
      </b-navbar>
    </div>

    <!-- Password Modal-->
    <b-modal ref="login-modal" id="login-modal" hide-header-close hide-footer no-close-on-esc no-close-on-backdrop title="Login to an Existing Account">
      <b-form>

        <b-form-group label="Username:">
          <b-form-input
              v-model="username"
              required
              placeholder="Username"
              :state="usernameState"
          ></b-form-input>
          <!-- Invalid Feedback -->
          <b-form-invalid-feedback id="input-live-feedback">
            Username must have at least 1 character and only include letters,numbers, and underscores
          </b-form-invalid-feedback>
        </b-form-group>

        <b-form-group>
          <b-form-input
              v-model="password"
              required
              :state="passwordState"
              placeholder="Password"
              type="password"
              max="60"
          ></b-form-input>
          <!-- Invalid Feedback -->
          <b-form-invalid-feedback id="input-live-feedback">
            Password must be between 6 and 60 characters
          </b-form-invalid-feedback>
        </b-form-group>

        <b-button class="float-right"  variant="primary" @click="login" :disabled="loginValid">Login</b-button>
        <b-button class="float-right mx-3" @click="hideModal">Cancel</b-button>

      </b-form>
    </b-modal>

    <!--register modal -->
    <b-modal ref="register-modal" id="register-modal" hide-header-close hide-footer no-close-on-esc no-close-on-backdrop title="Register a New Account">
      <b-form>

        <b-form-group label="Username:">
          <b-form-input
              v-model="username"
              required
              placeholder="Username"
              :state="usernameState"
          ></b-form-input>
          <!-- Invalid Feedback -->
          <b-form-invalid-feedback id="input-live-feedback">
            Username must have at least 1 character and only include letters,numbers, and underscores
          </b-form-invalid-feedback>
        </b-form-group>

        <b-form-group label="Email">
          <b-form-input
          v-model="email"
          required
          placeholder="Email"
          :state="emailState"
          >
          </b-form-input>
          <b-form-invalid-feedback id="input-live-feedback">
            Email must be a valid email address
          </b-form-invalid-feedback>
        </b-form-group>

        <b-form-group label="Password">
          <b-form-input
              v-model="password"
              required
              :state="passwordState"
              placeholder="Password"
              type="password"
              max="60"
          ></b-form-input>
          <b-form-invalid-feedback id="input-live-feedback">
            Password must be between 6 and 60 characters
          </b-form-invalid-feedback>
        </b-form-group>

        <b-button class="float-right"  variant="primary" @click="register" :disabled="loginValid">Register</b-button>
        <b-button class="float-right mx-3" @click="hideModal">Cancel</b-button>

      </b-form>
    </b-modal>

    <!-- Change Password Modal-->
    <b-modal ref="changePassword-modal" id="changePassword-modal" hide-header-close hide-footer no-close-on-esc no-close-on-backdrop title="Change your password">
      <b-form>
        <b-form-group>
          <b-form-input
              v-model="password"
              required
              :state="passwordState"
              placeholder="Password"
              type="password"
              max="60"
          ></b-form-input>
          <!-- Invalid Feedback -->
          <b-form-invalid-feedback id="input-live-feedback">
            Password must be between 6 and 60 characters
          </b-form-invalid-feedback>
        </b-form-group>

        <b-button class="float-right"  variant="primary" @click="changePassword" :disabled="!passwordState">Login</b-button>
        <b-button class="float-right mx-3" @click="hideModal">Cancel</b-button>
      </b-form>
    </b-modal>

    <!-- Change Password Modal-->
    <b-modal ref="delete-modal" id="delete-modal" hide-header-close hide-footer no-close-on-esc no-close-on-backdrop title="Delete your Account">
      <b-form>
        <h2>Are You sure you want to delete your account?</h2>
        <b-button class="float-right"  variant="danger" @click="deleteAccount">DELETE</b-button>
        <b-button class="float-right mx-3" @click="hideModal">Cancel</b-button>
      </b-form>
    </b-modal>

    <router-view class="container-xl"/>
  </div>
</template>

<script>
import GlobalMixin from "@/mixins/global-mixin";
  export default {

    mixins:[GlobalMixin],

    data(){
     return{
       username: '',
       password: '',
       email: '',
       token: '',
     }
    },

    methods:{

      hideModal(){
        this.password = '';
        this.username = '';
        this.email = '';
        this.invalid = false;
        this.$refs['login-modal'].hide();
        this.$refs['register-modal'].hide();
        this.$refs['changePassword-modal'].hide();
        this.$refs['delete-modal'].hide();
      },

      login(){
        if(this.passwordState && this.usernameState)
        {
          this.callAPI(this.USER_URL, 'GET',{username:this.username,password:this.password})
          .then(response=>{
            if(response.status === 200) {
              this.token = response.data;
              this.setCookie('token', this.token);
              this.setCookie('username', this.username);
              this.token = "";
              this.username = "";
              window.location.reload();
            }
          }).catch(()=>{
            alert("Username or Password is Invalid");
          });
        }
      },

      logout(){
        this.deleteCookie('username');
        this.deleteCookie('token');
        window.location.reload();
      },

      register() {
        if (this.passwordState && this.usernameState && this.emailState) {
          this.callAPI(this.USER_URL, 'POST', {username: this.username, password: this.password, email: this.email})
              .then(response => {
                if (response.status === 201) {
                  this.login();
                }
              }).catch(() => {
            alert("The Username or Email is already in use");
          })
        }
      },
      changePassword(){
        if (this.passwordState) {
          this.token = this.getCookie('token');
          this.username = this.getCookie('username');
          this.callAPI(this.USER_URL, 'PUT', {username: this.username, password: this.password,token: this.token})
              .then(() => {
                  this.logout();
              })
        }
      },
      deleteAccount(){
          this.token = this.getCookie('token');
          this.username = this.getCookie('username');
          this.callAPI(this.USER_URL, 'DELETE', {username: this.username,token: this.token})
              .then(() => {
                this.logout();
              })

      }

    },

    computed:{
      passwordState(){
       return this.password.length === 0 ? null : this.password.length >= 6 ?
           this.password.length <= 60: false
      },
      usernameState(){
       return this.username.length === 0 ? null : /^\w*$/.test(this.username);
      },
      emailState(){
       return this.email.length === 0 ? null : /\S+@\S+\.\S+/.test(this.email);
      },
      loginValid(){
        return ! (this.passwordState && this.username);
      },
      loggedIn(){
       return ! (this.getCookie('username') === "");
      }
    }
  }
</script>

<style scoped>
a, em{
  color:white;
}
</style>
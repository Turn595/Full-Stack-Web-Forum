const ARTICLE_URL = 'http://localhost:8003/index.php';
const USER_URL = 'http://localhost:8003/Users.php';
const COMMENT_URL = 'http://localhost:8003/Comments.php';
const TAG_URL = 'http://localhost:8003/Tags.php';

const GlobalMixin = ({
    data(){
        return {
            ARTICLE_URL,
            COMMENT_URL,
            USER_URL,
            TAG_URL
        }
    },
    methods:{
        //Cookie method were modifed from https://www.w3schools.com/js/js_cookies.asp
        setCookie(cname, cvalue) {
            document.cookie = cname + "=" + cvalue+";SameSite=Strict";
        },
        getCookie(cname) {
            let name = cname + "=";
            let decodedCookie = decodeURIComponent(document.cookie);
            let ca = decodedCookie.split(';');
            for(let i = 0; i <ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
            }
            return "";
        },
        deleteCookie(cname) {
            document.cookie = cname + "=" + "; expires=Thu, 01 Jan 1970 00:00:00 UTC;SameSite=Strict"
        },
        callAPI(URL,method, dataToSend){
            let config={
                method: method.toLowerCase(), //post,put,get, or delete - ensure lower case
                url: URL, //global const to student api url
                params:{} //empty object for now
            };

            //put and post use data attribute, delete and get use params attribute
            if(['put','post'].includes(config.method)){
                config.data = dataToSend; //axis sends data attribute as JSON formatted string in the BODY of the request
            } else {
                config.params = dataToSend //axios sends params through the url. example: localhost:8002?sort=lastName&order=asc
            }

            //if debug prop is true then send the xdebug param so PHPStorm can debug the php code - line by line
            if(this.debug){
                Object.assign(config.params,{XDEBUG_SESSION_START:'phpdebug'});
            }

            //return an Axios promise object we can use .then, .catch and .finally in our component
            return this.axios(config);
        }

    },

});

export default GlobalMixin

'use strict';

import Vue from './vendor/vue/dist/vue.esm.browser.js';


import MbApp from './components/app/app.component.js';


var app = new Vue({
    el: '#app',
    template:'<mb-app/>',
    components:{MbApp},
});


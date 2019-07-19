
export default {
    template: '#app-popup-template',
    props:{
        displayd:{
            type:Boolean,
            required:true,
        }
    },
    computed:{
        
    },
    methods:{
        close(){
            this.$emit('update:displayd', false);
        }
    }
};


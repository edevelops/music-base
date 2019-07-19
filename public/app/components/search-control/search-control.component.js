
export default {
    template: '#app-search-control-template',
    props:{
        value:{
            type:String,
            default:null,
        },
        placeholder:{
            type:String,
            required:true,
        },
        items:{
            type:Array,
            required:true,
        },
        labelProperty:{
            type:String,
            default:null,
        }
    },
    computed:{
        hasSlot(){
            return !!this.$scopedSlots['default'];
        },
        searchText:{
            get(){
                return this.value;
            },
            set(text){
                this.setValue(text);
            }
        },
        lowerCaseSearchText(){
            return this.value.toLowerCase();
        },
        filteredItems(){
            return this.value ? this.items.filter((item)=>{
                return this.getItemLabel(item).toLowerCase().indexOf(this.lowerCaseSearchText) > -1;
            }) : [];
        },
        allowClear(){
            return !!this.value;
        }
    },
    methods:{
        setValue(text){
            this.$emit('input', text);
        },
        clear(){
            this.setValue(null);
        },
        getItemLabel(item){
            return this.labelProperty ? item[this.labelProperty] : item;
        },
        selectItem(item){
            this.clear();
            this.$emit('select', item);
        }
    }
};


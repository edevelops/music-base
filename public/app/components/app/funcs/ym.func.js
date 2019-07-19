
export default {
    data(){
        return {
            ymImportOpened:false,
            ymImportData:'',
            ymImportResultsOpened:false,
            ymImportResults:null,
        };
    },
    methods:{
        ymImportOpen(){
            this.ymImportOpened=true;
        },
        ymImportApply(){
            this._apiPostCall('import', {data:this.ymImportData, source:'YM'}).then((data)=>{
                this.ymImportResults=data;
                this.ymImportOpened=false;
                this.ymImportResultsOpened=true;
                this.loadAll();
            });
        },
    },
};
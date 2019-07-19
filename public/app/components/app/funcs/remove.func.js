
export default {
    data(){
        return {
            removeTrack:null,
            removeOpened:false,
        };
    },
    methods:{
        removeOpen(track){
            this.removeOpened=true;
            this.removeTrack=track;
        },
        remove(fileOnly){
            this._apiDeleteCall('tracks/'+this.removeTrack.id+(fileOnly ? '/file' : '')).then(()=>{
                this.removeOpened=false;
                this.loadTracks();
            });
        },
        removeCancel(){
            this.removeOpened=false;
        },
    },
};
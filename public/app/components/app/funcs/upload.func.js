
export default {
    data(){
        return {
            uploadTrack:null,
            uploadOpened:false,
            uploadUrl:null,
        };
    },
    methods:{
        uploadOpen(track){
            this.uploadTrack=track;
            this.uploadOpened=true;
        },
        uploadApply(){
            const trackId=this.uploadTrack.id;
            this._apiPostCall('tracks/'+trackId+'/upload', {url:this.uploadUrl}).then(()=>{
                this.uploadOpened=false;
                this.loadTracks().then(()=>{
                    this.uploadTrack=null;
                    this.uploadUrl=null;
                    this.playerSelectTrack(this.tracksMap[trackId], true);
                });
            });
        },
    },
};
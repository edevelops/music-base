
export default {
    data(){
        return {
            trackInfoOpened:false,
            trackInfoTrack:null,
            trackInfoData:null,
        };
    },
    methods:{
        trackInfoOpen(track){
            this.trackInfoTrack=track;
            this._apiGetCall('tracks/'+track.id+'/info').then((data)=>{
                this.trackInfoOpened=true;
                this.trackInfoData=data;
            });
        },
    },
};
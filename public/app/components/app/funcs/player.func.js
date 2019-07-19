
export default {
    data(){
        return {
            playerMinPos:0,
            playerTrack:null,
            playerSource:null,
            playerRandom:true,
            playerPlaying:false,
            playerTime:0,
            playerVolume:100,
        };
    },
    computed:{
        playerMaxPos(){
            return this.playerDuration/2; // step=2s
        },
        playerPosLength(){
            return (this.playerMaxPos - this.playerMinPos);
        },
        playerDuration(){
            return this.playerTrack ? this.playerTrack.duration : 0;
        },
        playerPos(){
            return this.playerTime / this.playerDuration * this.playerPosLength;
        },
    },
    methods:{
        playerToogleRandom(){
            this.playerRandom=!this.playerRandom;
        },
        playerIsRandom(){
            return this.playerRandom;
        },
        _playerEl(){
            return this.$refs['player'];
        },
        playerRestoreState(){
            this.playerPlaying=window.localStorage.getItem('isPlayed')==='1';
            const time=window.localStorage.getItem('time');
            this.playerTime=(time ? Number(time) : 0);
        },
        playerSetPos(pos){
            this._playerSetTime(pos/this.playerPosLength*this.playerDuration);
            this._playserUpdatePosStatus();
        },
        playerSetVolume(val){
            this.playerVolume=val;
            this._playerEl().volume=Number(val)/100;
        },
        _playerSetPlaying(playing){
            this.playerPlaying=playing;
            window.localStorage.setItem('isPlayed', playing?'1':'0');
        },
        _playerSetTime(time){
            this.playerTime=time;
            window.localStorage.setItem('time', time);
        },
        playerSelectTrack(track, noAutoPlay){
            if(this.playerIsPlaying(track)){
                if(!noAutoPlay && !this.playerPlaying){
                    this.playerPlay();
                }
            }else{
                this._playerSetPlaying(!noAutoPlay ? true : this.playerPlaying);
                this._playerSetTime(0);
                this._generateNewHash({track});
            }
        },
        playerPlay(){
            this._playerSetPlaying(true);
            this._playserUpdatePlayStatus();
        },
        playerPause(){
            this._playerSetPlaying(false);
            this._playserUpdatePlayStatus();
        },
        _playserUpdatePosStatus(){
            const time=this.playerTime;
            const duration=this.playerDuration;
            if(duration && 0 <= time && time <= duration){
                this._playerEl().currentTime=time;
            }
        },
        _playserUpdatePlayStatus(){
            const player=this._playerEl();
            if(this.playerPlaying){
                player.play();
            }else{
                player.pause();
            }
        },
        playerGoTo(){
            if(this.playerTrack){
                const els=this.$refs['track-'+this.playerTrack.id];
                if(els && els[0]){
                    els[0].scrollIntoView();
                }
            }
        },
        playerPlayNext(){
            const tracks=this.playableTracks;
            if(tracks.length){
                const currentTrack=this.playerTrack;
                let nextTrack=null;
                if(this.playerRandom){
                    nextTrack=_(tracks).sample();
                }else{
                    const nextIndex=(_(tracks).findIndex(track=>track.id===currentTrack.id)+1)%tracks.length;
                    nextTrack=tracks[nextIndex ? nextIndex : 0]; // possible will not be found
                }
                this.playerSelectTrack(nextTrack);
            }
        },
        playerIsPlaying(track){
            return this.playerTrack && this.playerTrack.id===track.id;
        },
        playerOnHashChanged(params){
            const track=params.trackId ? this.tracksMap[params.trackId] : null;
            const lastTrack=this.playerTrack;
            if(track && (!lastTrack || lastTrack.id!==track.id)){
                this.playerTrack=track;
                this.playerGoTo();
                this._apiGetCall('tracks/'+track.id+'/file').then(({data})=>{
                    this.playerSource='data:audio/mpeg;base64,'+data;
                }).then(()=>{
                    setTimeout(()=>{ // FIXME: wait until tag will be ready?
                        this._playserUpdatePosStatus();
                        this._playserUpdatePlayStatus();
                    }, 100);
                });
            }
        },
    },
    mounted(){
        const player=this._playerEl();
        player.addEventListener('ended', ()=>{
            this.playerPlayNext();
        });
        player.addEventListener('timeupdate', ()=>{
            this._playerSetTime(player.currentTime);
        });
    },
};
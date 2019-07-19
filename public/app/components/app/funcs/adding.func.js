
export default {
    data(){
        return {
            addingOpened:false,
            addingTrack:null,
            addingAlbumTitle:null,
            addingArtistTitle:null,
            addingIsEditing:false,
        };
    },
    methods:{
        
        addingAddArtist(artist){
            const ids=this.addingTrack.artistIds;
            if(!_(ids).contains(artist.id)){
                ids.push(artist.id);
            }
        },
        addingSetAlbum(album){
            this.addingTrack.albumId=album.id;
        },
        addingClearAlbum(){
            this.addingTrack.albumId=null;
        },
        addingClearArtists(){
            this.addingTrack.artistIds=[];
        },
        addingOpen(){
            this.addingOpened=true;
            this.addingIsEditing=false;
            this.addingTrack={
                title:'',
                version:'',
                albumId:null,
                artistIds:[],
                tagIds:[],
            };
        },
        addingOpenEdit(track){
            this.addingOpened=true;
            this.addingIsEditing=true;
            this.addingTrack={ // clone
                ...track,
                artistIds:[...track.artistIds],
                tagIds:[...track.tagIds],
            };
        },
        addingApply(){
            const track=this.addingTrack;
            if(this.addingIsEditing){
                this._apiPatchCall('tracks/'+track.id, track).then((track)=>{
                    this.loadTracks().then(()=>{
                        this.addingOpened=false;
                    });
                });
            }else{
                this._apiPostCall('tracks', track).then((track)=>{
                    this.loadTracks().then(()=>{
                        this.addingOpened=false;
                        this.uploadOpen(this.tracksMap[track.id]);
                    });
                });
            }
        },
        addingAddAlbum(){
            this._apiPostCall('albums', {title:this.addingAlbumTitle}).then((album)=>{
                return this.loadAlbums().then(()=>{
                    this.addingTrack.albumId=album.id;
                    this.addingAlbumTitle=null;
                });
            });
        },
        addingAddTrack(){
            this._apiPostCall('artists', {title:this.addingArtistTitle}).then((artist)=>{
                return this.loadArtists().then(()=>{
                    this.addingTrack.artistIds.push(artist.id);
                    this.addingArtistTitle=null;
                });
            });
        },
        
    },
};
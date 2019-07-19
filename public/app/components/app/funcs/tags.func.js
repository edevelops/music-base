
import TagColor from '../classes/TagColor.js';

export default {
    data(){
        return {
            tagsOpened:false,
            
            tagsNewTag:null,
            
            tagsEditedTag:null,
            
            tagsColors:[
                TagColor.RED,
                TagColor.GREEN,
                TagColor.BLUE,
                TagColor.MAGENTA,
                TagColor.ORANGE,
                TagColor.BLACK,
            ],
            
            tagsTrackEdited:null,
            tagsTrackOpened:false,
            
        };
    },
    methods:{
                
        tagsTrackHasTag(tag){
            let ret=false;
            const track=this.tagsTrackEdited;
            if(track){
                ret=track.tagIds.indexOf(tag.id)!==-1;
            }
            return ret;
        },
        
        tagsTrackToggle(tag){
            const track=this.tagsTrackEdited;
            if(this.tagsTrackHasTag(tag)){
                track.tagIds=_(track.tagIds).without(tag.id);
            }else{
                track.tagIds=_(track.tagIds).union([tag.id]);
            }
            this._apiPatchCall('tracks/'+track.id, track);
        },
        
        tagsTrackOpen(track){
            this.tagsTrackEdited=track;
            this.tagsTrackOpened=true;
        },
        
        tagsCreate(){
            this._apiPostCall('tags', this.tagsNewTag).then(()=>{
                this.tagsNewTag=null;
                this.loadTags();
            });
        },
        
        tagsEdit(){
            this._apiPatchCall('tags/'+this.tagsEditedTag.id, this.tagsEditedTag).then(()=>{
                this.tagsEditedTag=null;
                this.loadTags();
            });
        },
        
        tagsOpenEdit(tag){
            this.tagsEditedTag={...tag};
        },
        
        tagsOpenCreate(){
            this.tagsNewTag={
                title:null,
                color:TagColor.BLACK
            };
        },
        
        tagsRemove(tag){
            if(confirm('Удалить тег "'+tag.title+'"?')){
                this._apiDeleteCall('tags/'+tag.id).then(()=>{
                    this.loadTags();
                });
            }
        },
        
        tagsOpen(){
            this.tagsOpened=true;
        },
        
    },
};
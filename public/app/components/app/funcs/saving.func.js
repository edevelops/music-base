
const Placeholders={
    ARTISTS:'{artists}',
    ARTISTS2:'{artists_}',
    TRACK:'{track}',
    TRACK2:'{track_}',
    ALBUM:'{album}',
    VERSION:'{version}',
    VERSION2:'{ (version)}',
    VERSION3:'{ - version}',
    VERSION4:'{_version_}',
    YEAR:'{year}',
};

const examples={
    
};


export default {
    data(){
        return {
            savingOpened:false,
            savingDir:null,
            savingTemplate:null,
            savingGroupByTags:false,
            savingPlaceholders:[
                Placeholders.ARTISTS,
                Placeholders.TRACK,
                Placeholders.TRACK2,
                Placeholders.ALBUM,
                Placeholders.VERSION,
                Placeholders.VERSION2,
                Placeholders.VERSION3,
                Placeholders.VERSION4,
                Placeholders.YEAR,
            ],
            savingExamples:[
                Placeholders.ARTISTS+'/'+Placeholders.ALBUM+'/'+Placeholders.TRACK,
                Placeholders.ARTISTS+'/'+Placeholders.ALBUM+'/'+Placeholders.TRACK+Placeholders.VERSION2,
                Placeholders.ARTISTS+'/'+Placeholders.YEAR+'/'+Placeholders.ALBUM+'/'+Placeholders.TRACK+Placeholders.VERSION2,
                Placeholders.ARTISTS+' - '+Placeholders.TRACK,
                Placeholders.ARTISTS+' - '+Placeholders.TRACK+Placeholders.VERSION2,
                Placeholders.ARTISTS2+'_-_'+Placeholders.TRACK2+Placeholders.VERSION4,
            ],
        };
    },
    methods:{
        savingSetExample(template){
            this.savingTemplate=template;
        },        
        savingApply(){
            const request={
                dir:this.savingDir,
                template:this.savingTemplate,
                tracks:this.filteredTracks.map(track => track.id),
                groupByTags:this.savingGroupByTags,
            };
            this._apiPostCall('save', request).then(()=>{
                this.savingOpened=false;
            });
        },
        savingOpen(){
            this.savingOpened=true;
        }
    },
};
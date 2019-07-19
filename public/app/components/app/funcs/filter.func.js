
import FilterType from '../classes/FilterType.js';

const OBJ_KEYS={
    [FilterType.ALBUM]:'album',
    [FilterType.TAG]:'tag',
    [FilterType.ARTIST]:'artist',
};

export default {
    data(){
        return {
            filters:[],
        };
    },
    computed:{
        filterDisplayed(){
            return this.filters.length;
        }
    },
    methods:{
        
        _filterMakeFilter(type, obj){
            return {
                type:type,
                value:obj.id,
                [OBJ_KEYS[type]]:obj,
            };
        },
        filterByTag(tag){
            this._filterAdd(this._filterMakeFilter(FilterType.TAG, tag));
        },
        filterByAlbum(album){
            this._filterAdd(this._filterMakeFilter(FilterType.ALBUM, album));
        },
        filterByArtist(artist){
            this._filterAdd(this._filterMakeFilter(FilterType.ARTIST, artist));
        },
        filterOnHashChanged(params){
            const resFilters=[];
            
            params.filters.forEach(({type, value})=>{
                let obj;
                switch(type){
                    case FilterType.ALBUM: obj=this.albumsMap[value]; break;
                    case FilterType.ARTIST: obj=this.artistsMap[value]; break;
                    case FilterType.TAG: obj=this.tagsMap[value]; break;
                }
                if(obj){
                    resFilters.push(this._filterMakeFilter(type, obj));
                }
            });
            
            this.filters=resFilters;
        },
        _filterAdd(filter){
            const found=_(this.filters).find((f)=>f.type===filter.type && f.value===filter.value);
            if(!found){
                this._generateNewHash({filters:this.filters.concat([filter])});
            }
        },
        filterRemove(filter){
            this._generateNewHash({filters:_(this.filters).without(filter)});
        },
    },
};
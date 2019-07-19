
import AppPopup from '../popup/popup.component.js';
import AppSearchControl from '../search-control/search-control.component.js';

import AppAddingFunc from './funcs/adding.func.js';
import AppUploadFunc from './funcs/upload.func.js';
import AppSavingFunc from './funcs/saving.func.js';
import AppFilterFunc from './funcs/filter.func.js';
import AppRemoveFunc from './funcs/remove.func.js';
import AppYmFunc from './funcs/ym.func.js';
import AppTrackInfoFunc from './funcs/trackInfo.func.js';
import AppPlayerFunc from './funcs/player.func.js';
import AppTagsFunc from './funcs/tags.func.js';

import FilterType from './classes/FilterType.js';

export default {
    template: '#app-component-template',
    components:{AppPopup, AppSearchControl},
    mixins:[AppAddingFunc, AppUploadFunc, AppSavingFunc, AppFilterFunc, AppRemoveFunc, AppYmFunc, AppTrackInfoFunc, AppPlayerFunc, AppTagsFunc],
    data(){
        return {
            FilterType,
            
            tracks:[],
            artists:[],
            albums:[],
            tags:[],
            
            loading:false,
            firstLoadFinished:false,
            loadErrorMessage:null,
            loadErrorDisplayed:false,
            
        };
    },
    computed:{
        // API for funcs
        tracksMap(){
            return _(this.tracks).indexBy('id');
        },
        // API for funcs
        albumsMap(){
            return _(this.albums).indexBy('id');
        },
        // API for funcs
        tagsMap(){
            return _(this.tags).indexBy('id');
        },
        // API for funcs
        artistsMap(){
            return _(this.artists).indexBy('id');
        },
        playableTracks(){
            return _(this.filteredTracks).filter(track=>track.hasFile);
        },
        playablePercent(){
            return this.playableTracks.length/this.tracks.length;
        },
        // API for funcs
        filteredTracks(){
            let ret=this.tracks;
            let filters=this.filters;
            if(filters.length){
                ret=ret.filter((track)=>{
                    return filters.every((filter)=>{
                        let passed=true;
                        switch(filter.type){
                            case FilterType.ALBUM: passed=(track.albumId===filter.album.id); break;
                            case FilterType.ARTIST: passed=_(track.artistIds).contains(filter.artist.id); break;
                            case FilterType.TAG: passed=_(track.tagIds).contains(filter.tag.id); break;
                        }
                        return passed;
                    });
                });
            }
            return ret;
        },
    },
    filters: {
        duration(value) {
            const mins=value ? Math.floor(value/60) : '';
            const secs=value ? Math.floor(value%60) : '';
            return value ? mins+':'+(secs < 10 ? '0': '')+secs : '';
        },
        album(album){
            return album.title+(album.year ? ' ('+album.year+')' : '' );
        },
        bitrate(value) {
            return value ? Math.round(value/1000) : '';
        },
        percent(value) {
            return value ? Math.floor(value*100)+'%' : '';
        },
    },
    methods:{
        
        _apiCall(method, route, data){
            this.loading=true;
            this.loadErrorMessage=null;
            this.loadErrorDisplayed=false;
            return axios[method]('/api/'+route, data).then(({data})=>{
                this.loading=false;
                return data;
            }).catch(({response})=>{
                this.loading=false;
                const {data, status}=response ? response : {};
                //console.log('err', {...err});
                this.loadErrorMessage=(data && data.message ? data.message : 'Error #'+status);
                this.loadErrorDisplayed=true;
                return Promise.reject();
            });
        },
        _apiGetCall(route, data){
            return this._apiCall('get', route, data);
        },
        _apiPostCall(route, data){
            return this._apiCall('post', route, data);
        },
        _apiDeleteCall(route){
            return this._apiCall('delete', route);
        },
        _apiPatchCall(route, data){
            return this._apiCall('patch', route, data);
        },
        
        // === Getters ===
        getTrackTags(track){
            const tagsMap=this.tagsMap;
            const ret=_(track.tagIds).chain().map((tagId)=>{
                return tagsMap[tagId];
            }).compact().value();
            return ret.length ? ret : [];
        },
        getTrackAlbum(track){
            const ret=this.albumsMap[track.albumId];
            return ret ? ret : null;
        },
        getTrackArtists(track){
            const artistsMap=this.artistsMap;
            const ret=_(track.artistIds).chain().map((artistId)=>{
                return artistsMap[artistId];
            }).compact().value();
            return ret.length ? ret : [];
        },
        
        // === State in URL hash === 
        
        _generateNewHash(params){
            const resultParams=[];
            const track=(params.track ? params.track : this.playerTrack);
            if(track){
                resultParams.push({key:'track', value:track.id});
            }
            const filters=(params.filters ? params.filters : this.filters);
            if(filters.length){
                resultParams.push({key:'filters', value:filters.map((filter)=>{
                    return filter.type+':'+filter.value;
                }).join(',')});
            }
            document.location.hash='!'+resultParams.map((pair)=>{
                return pair.key+'='+pair.value;
            }).join('&');
        },
        _parseHash(){
            const params={};
            const hashStr=document.location.hash;
            if(hashStr){
                hashStr.split(/#!|&/).forEach((item)=>{
                    if(item){
                        const pair=item.split('=');
                        if(pair){
                            params[pair[0]]=pair[1];
                        }
                    }
                });
            }
            function parseFilters(str){
                const ret=[];
                if(str){
                    str.split(',').forEach((item)=>{
                        if(item){
                            const pair=item.split(':');
                            if(pair){
                                ret.push({type:pair[0], value:pair[1]});
                            }
                        }
                    });
                }
                return ret;
            }
            return {
                filters:params['filters'] ? parseFilters(params['filters']) : [],
                trackId:params['track'] ? Number(params['track']) : null,
            };
        },
        _subscribeHash(){
            const onHashChanged=()=>{
                const params=this._parseHash();
                this.playerOnHashChanged(params);
                this.filterOnHashChanged(params);
            };
            
            window.addEventListener('hashchange', onHashChanged);
            onHashChanged();
        },
        // === Loading === 
        loadTracks(){
            return this._apiGetCall('tracks').then((data)=>{
                this.tracks=data;
            });
        },
        loadAlbums(){
            return this._apiGetCall('albums').then((data)=>{
                this.albums=data;
            });
        },
        loadTags(){
            return this._apiGetCall('tags').then((data)=>{
                this.tags=data;
            });
        },
        loadArtists(){
            return this._apiGetCall('artists').then((data)=>{
                this.artists=data;
            });
        },
        loadAll(){
            return Promise.all([
                this.loadTracks(),
                this.loadAlbums(),
                this.loadArtists(),
                this.loadTags(),
            ]);
        },
        reload(){
            this.loadAll();
        },
        
    },
    created(){
        this.loadAll().then(()=>{
            this.firstLoadFinished=true;
            this.playerRestoreState();
            this._subscribeHash();
        });
    }
    
};


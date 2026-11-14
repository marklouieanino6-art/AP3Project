function getAll(key){ return JSON.parse(localStorage.getItem(key)||'[]'); }
function saveAll(key, arr){ localStorage.setItem(key, JSON.stringify(arr)); }
function count(key){ return getAll(key).length; }
function add(key, obj){ const arr=getAll(key); obj.id = Date.now(); arr.push(obj); saveAll(key,arr); return obj; }
function update(key, id, obj){ const arr=getAll(key); const i=arr.findIndex(x=>x.id==id); if(i>=0){ arr[i]={...arr[i],...obj}; saveAll(key,arr);} }
function remove(key, id){ saveAll(key, getAll(key).filter(x=>x.id!=id)); }
function find(key, id){ return getAll(key).find(x=>x.id==id); }

// Seed sample data on first load
(function seed(){
  if(localStorage.getItem('seeded')) return;
  if(getAll('farmers').length===0){
    add('farmers',{name:'Juan dela Cruz',address:'Cebu City'});
    add('farmers',{name:'Maria Santos',address:'Mandaue'});
  }
  if(getAll('farms').length===0){
    add('farms',{name:'Green Valley Farm',location:'Cebu',irrigation:'Drip'});
    add('farms',{name:'Sunny Acres',location:'Mandaue',irrigation:'Sprinkler'});
  }
  if(getAll('crops').length===0){
    add('crops',{name:'Rice',category:'Grain'});
    add('crops',{name:'Corn',category:'Grain'});
    add('crops',{name:'Tomato',category:'Vegetable'});
  }
  if(getAll('plantings').length===0){
    add('plantings',{farm:'Green Valley Farm',crop:'Rice',status:'Planted'});
    add('plantings',{farm:'Sunny Acres',crop:'Corn',status:'Growing'});
  }
  if(getAll('harvests').length===0){
    add('harvests',{planting:1,quantity:120,remark:'Good Harvest'});
    add('harvests',{planting:2,quantity:80,remark:'Acceptable Quality'});
  }
  localStorage.setItem('seeded','1');
})();
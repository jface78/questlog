var weaponsArray = new Array();
var clericWeaponsArray = new Array();
var thiefWeaponsArray = new Array();
var wizardWeaponsArray = new Array();
var druidWeaponsArray = new Array();
var illusionistWeaponsArray = new Array();
var rangerWeaponsArray = new Array();
var fighterNWPsArray = new Array();
var fighterNWPsSlotsArray = new Array();
var priestNWPsArray = new Array();
var priestNWPsSlotsArray = new Array();
var thiefNWPsArray = new Array();
var thiefNWPsSlotsArray = new Array();
var mageNWPsArray = new Array();
var mageNWPsSlotsArray = new Array();
var generalNWPsArray = new Array();
var generalNWPsSlotsArray = new Array();

generalNWPsArray = ["Agriculture","Animal Handling","Animal Training","Artistic Ability","Blacksmithing","Brewing","Carpentry","Cobbling","Cooking","Dancing","Direction Sense","Etiquette","Fire-building","Fishing","Heraldry","Languages, Modern","LeatherWorking","Mining","Pottery","Riding, Airborne","Riding, Land-based","Rope Use","Seamanship","Seamstress/Tailor","Singing","Stonemasonry","Swimming","Weather Sense","Weaving"];
generalNWPsSlotsArray = ["1","1","1","1","1","1","1","1","1","1","1","1","1","1","1","1","1","2","1","2","1","1","1","1","1","1","1","1","1"];

priestNWPsArray = ["Ancient History","Astrology","Engineering","Healing","Herbalism","Languages, Ancient","Local History","Musical Instrument","Navigation","Reading/Writing","Religion","Spellcraft"];
priestNWPsSlotsArray = ["1","2","2","2","2","1","1","1","1","1","1","1"];

thiefNWPsArray = ["Ancient History","Appraising","Blind-fighting","Disguise","Forgery","Gaming","Gem Cutting","Juggling","Jumping","Local History","Musical Instrument","Reading Lips","Set Snares","Tightrope Walking","Tumbling","Ventriloquism"];
thiefNWPsSlotsArray = ["1","1","2","1","1","1","2","1","1","1","1","2","1","1","1","1"];

fighterNWPsArray = ["Animal Lore","Armorer","Blind-fighting","Bowyer/Fletcher","Charioteering","Endurance","Gaming","Hunting","Mountaineering","Navigation","Running","Set Snares","Survival","Tracking","Weaponsmithing"];
fighterNWPsSlotsArray = ["1","2","2","1","1","2","1","1","1","1","1","1","2","2","3"];

mageNWPsArray = ["Ancient History","Astrology","Engineering","Gem Cutting","Herbalism","Languages, Ancient","Navigation","Reading/Writing","Religion","Spellcraft"];
mageNWPsSlotsArray = ["1","2","2","2","2","1","1","1","1","1"];


fighterWeaponsArray = ["Arquebus","Battle Axe", "Blowgun","Composite Long Bow","Composite Short Bow","Long Bow","Short Bow","Club","Hand Crossbow","Heavy Crossbow","Light Crossbow","Dagger","Dart","Footman's Flail","Footman's Mace","Footman's Pick","Hand Axe","Harpoon","Horseman's Flail","Horseman's Mace","Horseman's Pick","Javelin","Heavy Horse Lance","Light Horse Lance","Jousting Lance","Medium Horse Lance","Mancatcher","Morning Star","Awl Pike","Bardiche","Bec de Corbin","Bill-Guisarme","Fauchard","Fauchard-Fork","Glaive","Glaive-Guisarme","Guisarme","Guisarme-Voulge","Halberd","Hook Fauchard","Lucern Hammer","Military Fork","Partisan","Ranseur","Spetum","Voulge","Quarterstaff","Scourge","Sickle","Sling","Spear","Staff Sling","Bastard Sword","Broad Sword","Khopesh","Long Sword","Scimitar","Short Sword","Two-Handed Sword","Trident","Warhammer","Whip"];
fighterWeaponsArray.sort();

clericWeaponsArray = ["Club","Footman's Flail","Footman's Mace","Horseman's Flail","Horseman's Mace","Morning Star","Quarterstaff","Sling","Warhammer"];
clericWeaponsArray.sort();

thiefWeaponsArray = ["Club","Dagger","Dart","Hand Crossbow","Short Bow","Sling","Broad Sword","Long Sword","Short Sword","Quarterstaff"];
thiefWeaponsArray.sort();

druidWeaponsArray = ["Club","Sickle","Dart","Dagger","Spear","Scimitar","Sling","Quarterstaff"];
druidWeaponsArray.sort();

wizardWeaponsArray = ["Dagger","Quarterstaff","Staff Sling","Sling","Dart"];
wizardWeaponsArray.sort();

illusionistWeaponsArray = wizardWeaponsArray;
rangerWeaponsArray = fighterWeaponsArray;

function getNWPs(){
  var generalArray = new Array();
  var fighterArray = new Array();
  var thiefArray = new Array();
  var priestArray = new Array();
  var mageArray = new Array();
  generalArray[0] = generalNWPsArray;
  generalArray[1] = generalNWPsSlotsArray;
  fighterArray[0] = fighterNWPsArray;
  fighterArray[1] = fighterNWPsSlotsArray;
  thiefArray[0] = thiefNWPsArray;
  thiefArray[1] = thiefNWPsSlotsArray;
  priestArray[0] = priestNWPsArray;
  priestArray[1] = priestNWPsSlotsArray;
  mageArray[0] = mageNWPsArray;
  mageArray[1] = mageNWPsSlotsArray;
  var arr = new Array();
  arr[0] = generalArray;
  arr[1] = fighterArray;
  arr[2] = thiefArray;
  arr[3] = priestArray;
  arr[4] = mageArray;
  return arr;
}

function getWeapons(charClass){
  var multi = new Array();
  var arr = new Array();
  var pickedArr = false;
  multi = charClass.split("/");
  if (multi.length == 1){
    switch(charClass){
      case "fighter":
      arr = fighterWeaponsArray;
      break;
      case "paladin":
      arr = fighterWeaponsArray;
      break;
      case "ranger":
      arr = fighterWeaponsArray;
      break;
      case "bard":
      arr = fighterWeaponsArray;
      break;
      case "cleric":
      arr = clericWeaponsArray;
      break;
      case "druid":
      arr = druidWeaponsArray;
      break;
      case "thief":
      arr = thiefWeaponsArray;
      break;
      case "mage":
      arr = wizardWeaponsArray;
      break;
      case "illusionist":
      arr = wizardWeaponsArray;
      break;
    }
  }
  else{
    if (checkClassPresent(charClass,"cleric") == true){
      arr = clericWeaponsArray;
      pickedArr = true;
    }
    else if(checkClassPresent(charClass,"druid") == true){
      arr = druidWeaponsArray;
      pickedArr = true;
    }
    else if ( ( checkClassPresent(charClass,"fighter") || (checkClassPresent(charClass,"ranger") == true) ) && (pickedArr == false) ){
      arr = fighterWeaponsArray;
    }
    else{
      var tmpArr = new Array();
      for (var i=0;i<multi.length;i++){
        tmpArr.push(eval(multi[i] + "WeaponsArray"));
      }
      var newArr = new Array();
      for (var s=0;s<tmpArr.length;s++){
        for (var q=0;q<tmpArr[s].length;q++){
          newArr.push(tmpArr[s][q]);
        }
      }
      arr = newArr.unique();
      arr.sort();
    }
  }
  return arr;
}
function getWeaponStats(weapon){
	if (weapon.indexOf("(specialized)")>-1){
		var arr = weapon.split("(specialized)");
		weapon = arr[0];
	}
	if (weapon.indexOf("+") > -1){
		var arr = weapon.split("+");
		weapon = arr[0];
	}
	if (weapon.indexOf("(untrained)") > -1){
		var arr = weapon.split("(untrained)");
		weapon = arr[0];
	}
	if (weapon.indexOf("(proficient)") > -1){
		var arr = weapon.split("(proficient)");
		weapon = arr[0];
	}
	weapon = weapon.trim();
	var weaponStats = new Array();
	switch (weapon){
		case "Battle Axe":
		weaponStats.push("M",7,"1d8","1d8");
		break;
		case "Blowgun":
		weaponStats.push("L",5,1,1);
		break;
		case "Composite Long Bow":
		weaponStats.push("L",7,"1d8","1d8");
		break;
		case "Composite Short Bow":
		weaponStats.push("M",6,"1d8","1d8");
		break;
		case "Long Bow":
		weaponStats.push("L",8,"1d8","1d8");
		break;
		case "Short Bow":
		weaponStats.push("M",7,"1d8","1d8");
		break;
		case "Club":
		weaponStats.push("M",4,"1d6","1d3");
		break;
		case "Hand Crossbow":
		weaponStats.push("S",5,"1d4","1d4");
		break;
		case "Heavy Crossbow":
		weaponStats.push("M",10,"1d4","1d4");
		break;
		case "Light Crossbow":
		weaponStats.push("M",7,"1d4","1d4");
		break;
		case "Dagger":
		weaponStats.push("S",2,"1d4","1d3");
		break;
		case "Dart":
		weaponStats.push("S",2,"1d3","1d2");
		break;
		case "Footman's Flail":
		weaponStats.push("M",7,"1d6+1","2d4");
		break;
		case "Footman's Mace":
		weaponStats.push("M",7,"1d6+1","1d6");
		break;
		case "Footman's Pick":
		weaponStats.push("M",7,"1d6+1","1d6");
		break;
		case "Hand Axe":
                weaponStats.push("M",4,"1d6","1d4");
                break;
                case "Harpoon":
                weaponStats.push("L",7,"2d4","2d6");
                break;
                case "Hand Axe":
                weaponStats.push("M",4,"1d6","1d4");
                break;
                case "Horseman's Flail":
		weaponStats.push("M",6,"1d4+1","1d4+1");
		break;
		case "Horseman's Mace":
		weaponStats.push("M",6,"1d6","1d4");
		break;
		case "Horseman's Pick":
		weaponStats.push("M",5,"1d4+1","1d4");
		break;
		case "Javelin":
		weaponStats.push("M",4,"1d6","1d6");
		break;
                case "Heavy Horse Lance":
		weaponStats.push("L",8,"1d8+1","3d6");
		break;
		case "Light Horse Lance":
		weaponStats.push("L",6,"1d6","1d8");
		break;
		case "Jousting Lance":
		weaponStats.push("L",10,"1d3-1","1d2-1");
		break;
		case "Medium Horse Lance":
		weaponStats.push("L",7,"1d6+1","2d6");
		break;
		case "Mancatcher":
		weaponStats.push("L",7,"-","-");
		break;
		case "Morning Star":
		weaponStats.push("M",7,"2d4","1d6+1");
		break;
		case "Awl Pike":
		weaponStats.push("L",13,"1d6","1d12");
		break;
		case "Bardiche":
		weaponStats.push("L",9,"2d4","2d6");
		break;
		case "Bec de Corbin":
		weaponStats.push("L",9,"1d8","1d6");
		break;
		case "Bill-Guisarme":
		weaponStats.push("L",10,"2d4","1d10");
		break;
		case "Fauchard":
		weaponStats.push("L",8,"1d6","1d8");
		break;
		case "Fauchard-Fork":
		weaponStats.push("L",8,"1d8","1d10");
		break;
		case "Glaive":
		weaponStats.push("L",8,"1d6","1d10");
		break;
		case "Glaive-Guisarme":
		weaponStats.push("L",9,"2d4","2d6");
		break;
		case "Guisarme":
		weaponStats.push("L",8,"2d4","1d8");
		break;
		case "Guisarme-Voulge":
		weaponStats.push("L",10,"2d4","2d4");
		break;
		case "Halberd":
		weaponStats.push("L",9,"1d10","2d6");
		break;
		case "Hook Fauchard":
		weaponStats.push("L",9,"1d4","1d4");
		break;
		case "Lucern Hammer":
		weaponStats.push("L",9,"2d4","1d6");
		break;
		case "Military Fork":
		weaponStats.push("L",7,"1d8","2d4");
		break;
		case "Partisan":
		weaponStats.push("L",9,"1d6","1d6+1");
		break;
		case "Ranseur":
		weaponStats.push("L",8,"2d4","2d4");
		break;
		case "Spetum":
		weaponStats.push("L",8,"1d6+1","2d6");
		break;
		case "Voulge":
		weaponStats.push("L",10,"2d4","2d4");
		break;
		case "Quarterstaff":
		weaponStats.push("L",4,"1d6","1d6");
		break;
		case "Scourge":
		weaponStats.push("S",5,"1d4","1d2");
		break;
		case "Sickle":
		weaponStats.push("S",4,"1d4+1","1d4");
		break;
		case "Sling":
		weaponStats.push("S",6,"1d4+1","1d6+1");
		break;
		case "Spear":
		weaponStats.push("M",6,"1d6","1d8");
		break;
		case "Staff Sling":
		weaponStats.push("M",11,"1d4+1","1d6+1");
		break;
		case "Bastard Sword":
		weaponStats.push("M",6,"1d8","1d12");
		break;
		case "Broad Sword":
		weaponStats.push("M",5,"2d4","1d6+1");
		break;
		case "Khopesh":
		weaponStats.push("M",9,"2d4","1d6");
		break;
		case "Long Sword":
		weaponStats.push("M",5,"1d8","1d12");
		break;
		case "Scimitar":
		weaponStats.push("M",5,"1d8","1d8");
		break;
		case "Short Sword":
		weaponStats.push("S",3,"1d6","1d8");
		break;
		case "Two-Handed Sword":
		weaponStats.push("L",10,"1d10","3d6");
		break;
		case "Trident":
		weaponStats.push("L",7,"1d6+1","3d4");
		break;
		case "Warhammer":
		weaponStats.push("M",4,"1d4+1","1d4");
		break;
		case "Whip":
		weaponStats.push("M",8,"1d2","1");
		break;
	}
	return weaponStats;
}
Array.prototype.unique = function () {
	var r = new Array();
	o:for(var i = 0, n = this.length; i < n; i++)
	{
		for(var x = 0, y = r.length; x < y; x++)
		{
			if(r[x]==this[i])
			{
				continue o;
			}
		}
		r[r.length] = this[i];
	}
	return r;
}
String.prototype.trim = function () {
    return this.replace(/^\s*/, "").replace(/\s*$/, "");
}

function checkClassPresent(theClass,type){
  var splitter = theClass.split("/");
  var matched;
  for (var i=0;i<splitter.length;i++){
    if ( splitter[i] == type) {
      matched = true;
    }
  }
  if ( (matched == "") || (matched == undefined) ){
    return false;
  }
  else{
    return matched;
  }
}
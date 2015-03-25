
function RandomNPC() {
  this.isDumb = false;
  this.isSmart = false;
  this.isStrong = false;
  this.isWeak = false;
  this.isUgly = false;
  this.isPretty = false;
  this.race = null;
  this.gender = null;
  this.name = undefined;
  this.age = null;
  this.job = null;
  this.jobSkill = null;
  this.trait1 = null;
  this.trait2 = null;
  this.title = '';
  this.description = '';
  

var elfPrefix = ["al","alagos","alph","ar","arn","arod","arth","bain","bass","beleg","brann","breg","calen","canad","caran","celeb","coru",
                        "cull","curu","cu","dae","dinen","dol","dur","edhel","esgal","el","fael","finn","galad","galadh","gil","hall","hand","heleg",
                        "helehui","him","ist","laer","lain","lass","loth","luin","maen","maethor","mall","mell","mellon","meren","meril","min","mith",
                        "mir","mor","naur","nel","nen","nim","oll","orn","palan","pilin","prestad","prestol","sael","sigil","silif","tad","tathar","thand",
                        "thenin","thent","thurin","tu"];


var elfSuffix = ["khirille","jelle","inia","thelre","anwa","khir","jondo","eth","hel","hiril","iel","inu","nes","ni","nis","thel","ves","wen","anu",
                        "dor","hir","ion","nir","on","ven","al","alagos","alph","ar","arn","arod","arth","bain","bass","beleg","brann","breg","calen",
                        "canad","caran","celeb","coru","cull","curu","cu","dae","dinen","dol","dur","edhel","esgal","el","fael","finn","galad","galadh",
                        "gil","hall","hand","heleg","helehui","him","ist","laer","lain","lass","loth","luin","maen","maethor","mall","mell","mellon",
                        "meren","meril","min","mith","mir","mor","naur","nel","nen","nim","oll","orn","palan","pilin","prestad","prestol","sael","sigil",
                        "silif","tad","tathar","thand","thenin","thent","thurin","tu"];

var humanFirstNameM = ["burt", "biff", "kurt", "simon", "carl", "woodrow", "gus", "vic", "barney", "bill", "henry", "harold", "wilson", "newt", "george", "michael", "colin", "colby", "noah", "mason", "ethan", "ben", "logan", "luke", "sam", "dylan", "isaac","carter", "jack", "tom", "harry", "thomas", "clay",
"parker", "grayson", "nolan", "hudson", "cranston","robert","james","jim","ed", "edward", "calvin","walter","arthur","ralph","fred","roy","melvin","edwin","edmund","gordon","cecil","harvey","oscar","curtis"];
var humanFirstNameF = ["mary", "sue", "emma","isabella","ava","mia","emily","abigail","charlotte","martha","harper","evelyn","eunice","victoria","lily", "lillian","scarlet","peyton","claire","penelope", "penny","lucy","violet","anne", "anna", "annabelle","eve", "eva","faith","ruby","dorothy","margaret","mildred","eliza","alice","marjorie","rose","gladys","edith","hazel","bernice","dolores","gertrude","elsie","agnes","wilma","bertha","wanda","bessie","minnie","rosemary","olga","bette","daisy","petunia"];
var humanLastNamePrefix = ["gold","ender","fair","wex","stump","gor","silver","thumb","coal","wine","black","dingle","dunn","dor","fish","ratt","dogg","brick"];
var humanLastNameSuffix = ["man","son","berry","field","er","house","castle","tower","wall","ins","wife"];
var dwarfFirstPrefix = ["a","an","ar","az","b","bel","bar","baz","bof","bol","d","dar","del","dol","dor","dw","duer","dur","el","er","fal","far","gar","gil",
                                    "gim","glan","glor","har","jar","kil","ma","mor","nal","nor","nur","o","or","ov","rei","th","tho","thr","tor","ur","val","von","whur",
                                    "wer","yur"];
var dwarfFirstSuffix =  ["aim","ain","ak","ar","auk","bere","bir","dak","dal","din","el","ent","erl","gal","gar","gan","gen","grim","gur","ias","i","ili","im",
                                    "in","ir","kas","kral","lond","o","on","or","oril","rak","ral","ric","rid","rim","ring","ster","sun","ten","thal","then","thic","thur",
                                    "ut","ur","urt","val","var","ack","arr","bek","cral","dar","duum","dukr","eft","erg","est","fik","gak","girn","gyth","hak","hig",
                                    "jak","jyr","kak","krak","lagg","lode","lyr","malk","mek","nore","rak","ral","sten","tek","vir","zak"];

var dwarfLastPref = ["gold","silver","lode","copper","brass","tin","steel","iron","lead","stone","coal","gem","ruby","diamond","emerald","granite",
                                 "rock","cinder","jade","shale","slate","quartz","onyx","glass","black","gray","white","red","green","good","bad","wicked","pure"];
var dwarfLastSuff = ["fire","shield","cliff","crag","whisker","sword","hammer","axe","forge","beard","helm","chest","head","arm","back","mountain",
                                 "hill","home","blood","sun","moon","eye","nose","mouth","ear","blade","neck","house","castle","keep","hearth","heart"];

var gnomePrefix = ["beren","blee","bunk","cobb","cory","daer","dal","elly","el","fol","fon","fud","glim","hed","hodge","jeb","klem","lind","loop","lum",
                               "minni","nackle","nin","pil","pun","ranz","raul","roon","roy","schep","see","sha","tur","bimp","bodd","cara","dwobb","dimb","fud",
                               "ger","mard","mibbi","mur","nam","ner","see","wim","zook"];
var gnomeSuffix = ["biddle","gel","kin","mut","nig","ni","madge","pen","jonottin","nottin","wyn","nock","le","wicket","ger","bell","bert","nab","kor",
                                "mut","zig","foodle","wick","nab","jon","wocket","o","winkle","pest","podge","ji","yo","mil","bur","to","dar","man","twiss","twist",
                                "nor","mottin","biddle","rick"];

var dumbNames = ["witless","slow","drooler","idiot","dull","stupid","dense","dim","thick","dazed","fool","dullard"];
var smartNames = ["brilliant","ponderous","thinker","clever","shrewd","canny","acute","quick-witted","knowing"];
var strongNames = ["strong","mighty","powerful","robust","brawny","potent","hardy","stalwart","sturdy","ironman","vigorous"];
var weakNames = ["feeble","weak","delicate","pale","scrawny","frail","fragile","infirm","decrepit","wasted"];
var uglyNames = ["ugly","foul","hideous","disfigured","repulsive","revolting","unsightly","appalling","unlovely","deformed",
                             "homely","repugnant","misshapen","grotesque","stinker","butt"];
var prettyNames = ["beautiful","beauteous","lovely","comely","fair","handsome","pretty","graceful","gorgeous","ravishing","stunning","hot"];

 var traits=['able','absent-minded','active','adventurous','affable','affected','affectionate','afraid','aggressive','alert','ambitious',
 'amiable','angry','animated','annoyed','anxious','apologetic','appreciative','argumentative','arrogant','attentive','austere',
 'awkward','babyish','bashful','bewildered','blasé','blowhard','boastful','bold','boorish','bored','bossy','brave',
 'brutish','busy','calm','candid','capable','carefree','careful','careless','caring','caustic','cautious','changeable',
 'cheerful','civilised','clumsy','coarse','cold-hearted','committed','communicative','compassionate','competent',
 'complacent','conceited','concerned','confident','confused','conscientious','considerate','consistent','contented','cooperative',
 'courageous','cowardly','creative','critical','cross','cruel','cultured','curious','dainty','dangerous','daring','dark','dauntless',
'deceitful','decisive','deferential','demanding','demanding','dependable','depressed','desiccated','despondent','determined','devoted','diligent',
 'disaffected','disagreeable','discerning','discontented','discouraged','discreet','dishonest','disillusioned','disloyal','dismayed',
 'disorganized','disparaging','disrespectful','dissatisfied','distressed','domineering','doubtful','dreamy','dull','dutiful','eager',
 'easygoing','effervescent','efficient','embarrassed','encouraging','energetic','enthusiastic','equable','ethical','evil','exacting',
 'excessive','excitable','excited','expert','exuberant','facetious','fair','faithful','faithless','fanciful','fearful','fearless','feisty','ferocious',
 'fidgety','fierce','finicky','flexible','forgetful','forgiving','formal','fortunate','foul','frank','fresh','friendly','frightened','frustrated',
 'fun-loving','funny','furious','fussy','garrulous','generous','gentle','giddy','giving','gloomy','glum','good','greedy',
 'gregarious','grouchy','grumpy','guilty','gullible','happy','hard-working','hardy','harried','harsh','hateful','haughty','healthy','helpful',
 'hesitant','honest','hopeful','hopeless','hospitable','hot-tempered','humble','humorous','ignorant','ill-bred','imaginative','immature',
 'immobile','impartial','impatient','impolite','impudent','impulsive','inactive','inconsiderate','inconsistent','indecisive','independent',
 'indiscriminate','indolent','industrious','inefficient','innocent','insecure','insincere','insipid','insistent','insolent',
 'intolerant','intrepid','inventive','jealous','jolly','jovial','joyful','keen','kind','kindly','lackadaisical','languid','lazy','leader','licentious',
 'light','light-hearted','limited','lively','lonely','loquacious','loud','lovable','loving','loyal','lucky','malicious','mannerly',
 'mature','mean','meek','merciful','messy','meticulous','mischievous','miserable','moody','mysterious','nagging','naïve','naughty','neat',
 'negligent','nervous','nice','purposeless','noisy','not trustworthy','obedient','obliging','observant','open','optimistic','organised',
 'outspoken','overweight','patient','patriotic','peaceful','perserverant','persistent','persuasive','perverse','pessimistic','picky','pitiful',
 'plain','playful','pleasant','pleasing','polite','poor','popular','positive','precise','prim','primitive','proper','proud','prudent','punctual',
 'purposeful','quarrelsome','quick','quick-tempered','quiet','rational','reasonable','reckless',
 'relaxed','reliable','religious','repugnant','repulsive','reserved','resourceful','respectful','responsible','restless','rich','rigid','risk-taking','rough',
 'rowdy','rude','ruthless','sad','safe','satisfied','scared','scatty','scheming','secretive','secure','self-centered','self-confident',
 'self-controlling','selfish','sensitive','sentimental','serious','shiftless','shy','silly','simple','sincere','skillful',
 'sneaky','soft-hearted','solitary','sorry','spendthrift','spoiled','sterile','stern','stingy','strange','strict',
 'stubborn','studious','submissive','successful','superstitious','supportive','suspicious','sweet','tactful','tactless','talented','talkative',
 'tardy','temperate','thankful','thorough','thoughtful','thoughtless','thrifty','thrilled','timid','tired','tireless','tolerant','touchy','tough',
 'trusting','trustworthy','truthful','unconcerned','uncoordinated','undependable','understanding','unforgiving','unfriendly','ungrateful',
 'unhappy','unkind','unmerciful','unselfish','unsuitable','upset','useful','vacant','violent','virtuous','warm','weak','wicked','wild','wise','wishy-washy','withdrawn','worried','wrong','youthful','zany',] 

 var commonTrades = ["servant", "thief", "warrior", "blacksmith", "merchant","pickpocket","slave", "mugger","priest","monk","farmer","hunter","prostitute","guard","tinker","tailor","mercenary","soldier","bard","bartender","cooper","scholar", "adventurer","apothecary","scavenger","librarian","clerk","schoolteacher","factory worker","coachman","liveryman","beggar",
 "brewer","carpenter","cobbler","cook","fisher","herald","miner","painter","sailor","mason","wainwright","shipwright","butcher"];
 var lessCommonTrades = ["chef","knight","ambassador","governor","mayor","nobleman","actor","artist","ship captain","caravan master",
 "courtesan","necromancer","wizard","slavemaster","gladiator","harbormaster","guildmaster","paladin","ranger"];
 var rareTrades = ["king","archbishop","archmage","emperor","chieftain"];
 var monsterJobs = ["chieftain", "shaman", "witchdoctor", "warrior", "hunter", "thief", "raider","slave trader","mugger","brigand","bandit","slave"];
 
 var monsterRaces = ["orc","goblin","troll","ogre"];
 var monsterNames = ["ogg", "nuk", "grak", "cor", "thud", "burd", "cro", "den", "ock", "mor", "dun", "starn", "vex", "pud", "duff", "nash", "pox",
                                    "grel", "tosh", "del", "rok", "muk", "fud", "nik", "nek", "voc", "grel", "kord", "zord", "lof", "hud", "pock", "zukk","gnash"];
 

var hairColors = ['ash-colored', 'bronze-colored', 'ginger', 'golden', 'honey-colored','flaxen', 'platinum','tawny','wheat-colored','auburn',
                  'copper-colored','flaming red','sandy','chestnut','russet-colored','jet black','sooty black','salt and pepper','iron gray',
                  'silver','pearly white','snowy white'];
var hairStyles = ['an afro','a bob-cut','a bowl cut','braids','a buzz cut','a chonmage','a comb-over','a cornrow','a crewcut','a crown braid',
                  'a curtained style','a devil lock','dreadlocks','a feathery style','a flattop','a french braid','a hi-top','a jheri curl','spikes',
                  'a mullet','a mohawk','a mop-top','a pageboy','pigtails','a pompadour','a ponytail','a rat tail','a slicked back style', 'bald'];
var beardStyles = ['flowing', 'chin curtain','chin strap','mutton chop','goat patch','goatee','soul patch','stubble'];
var mustacheStyles = ['handlebar','horseshoe','walrus','pencil','toothbrush','Fu Manchu'];

this.generate = function() {
  this.makeCharacterVitals();
  this.makeCharacterTraits();
  this.makeCharacterPhys();
}

this.makeCharacterVitals = function() {
  switch(getRandomInt(1,2)) {
    case 1:
      this.gender = 'male';
      break;
    case 2:
      this.gender = 'female';
      break;
  }
  var prefixArray, suffixArray;
  
  //50% gnome
  //25% human
  //15% dwarf
  //5% elf
  //5% other
  if (!this.race) {
    var racePercent = getRandomInt(0,99);
    if (racePercent <= 49) {
      this.race = 'gnome';
    } else if (racePercent <= 74) {
      this.race = 'human';
    } else if (racePercent <= 89) {
      this.race = 'dwarf';
    } else if (racePercent <= 94) {
      this.race = 'elf';
    }
  }
  switch(this.race) {
    case 'gnome':
      this.name = ucwords(gnomePrefix[getRandomInt(0,gnomePrefix.length-1)] + gnomeSuffix[getRandomInt(0,gnomeSuffix.length-1)]);
      break;
    case 'human':
      if (this.gender == 'male') {
        this.name = ucwords(humanFirstNameM[getRandomInt(0,humanFirstNameM.length-1)]);
        if (getRandomInt(1,4) == 1) {
          this.name += ' ' + ucwords(humanFirstNameM[getRandomInt(0,humanFirstNameM.length-1)]);
        }
      } else {
        this.name = ucwords(humanFirstNameF[getRandomInt(0,humanFirstNameF.length-1)]);
        if (getRandomInt(1,4) == 1) {
          this.name += ' ' + ucwords(humanFirstNameF[getRandomInt(0,humanFirstNameF.length-1)]);
        }
      }
      break;
    case 'dwarf':
      this.name = ucwords(dwarfFirstPrefix[getRandomInt(0,dwarfFirstPrefix.length-1)] + dwarfFirstSuffix[getRandomInt(0,dwarfFirstSuffix.length-1)]);
      break;
    case 'elf':
      this.name = ucwords(elfPrefix[getRandomInt(0,elfPrefix.length-1)] + elfSuffix[getRandomInt(0,elfSuffix.length-1)]);
      break;
    default:
      this.race = monsterRaces[getRandomInt(0, monsterRaces.length-1)];
      this.name = monsterNames[getRandomInt(0,monsterNames.length-1)];
      var syllables = getRandomInt(0, 4);
      for (var i=0; i < syllables; i++) {
        switch(getRandomInt(0,3)) {
          case 0:
            this.name += ' ';
            break;
          case 1:
            this.name += '~';
            break;
          case 2:
            this.name += "'";
            break;
        }
        this.name += monsterNames[getRandomInt(0,monsterNames.length-1)];
      }
      break;
  }

  if (this.gender == 'female' && this.race != 'human' && this.race != 'gnome' && monsterRaces.indexOf(this.race) == -1 ) {
    this.name = feminizeThat(this.name);
  }
  if (this.race == 'dwarf') {
    this.name += ' ' + ucwords(dwarfLastPref[getRandomInt(0,dwarfLastPref.length-1)] + dwarfLastSuff[getRandomInt(0,dwarfLastSuff.length-1)]);
  }
  if (this.race == 'human') {
    this.name += ' ' + ucwords(humanLastNamePrefix[getRandomInt(0,humanLastNamePrefix.length-1)]);
    if (getRandomInt(1,2) == 1) {
      this.name += humanLastNameSuffix[getRandomInt(0,humanLastNameSuffix.length-1)];
    }
  }
  var ageProb = getRandomInt(0,9);
  if (ageProb <= 3) {
      this.age = 'young';
  } else if (ageProb <= 7) {
      this.age = 'middle-aged';
  } else {
      this.age = 'elderly';
  }
};

this.makeCharacterPhys = function() {
  var descriptors = [];
  if (this.race == 'dwarf' && getRandomInt(0,9) <= 2) {
    this.isStrong = true;
    descriptors.push('strong');
  } else if (this.race == 'human' && getRandomInt(0,9) <= 1) {
    this.isStrong = true;
    descriptors.push('strong');
  } else if (getRandomInt(0,9) == 0) {
    this.isStrong = true;
    descriptors.push('strong');
  }
  if (this.race == 'gnome' && getRandomInt(0,9) <= 2 && !this.isStrong) {
    this.isWeak = true;
    descriptors.push('weak');
  } else if (this.race == 'human' && getRandomInt(0,9) <= 1 && !this.isStrong) {
    this.isWeak = true;
    descriptors.push('weak');
  } else if (getRandomInt(0,9) == 0 && !this.isStrong) {
    this.isWeak = true;
    descriptors.push('weak');
  }
  if ((this.race == 'gnome' || this.race == 'elf') && getRandomInt(0,9) <= 2) {
    this.isSmart = true;
    descriptors.push('smart');
  } else if (this.race == 'human' && getRandomInt(0,9) <= 1) {
    this.isSmart = true;
    descriptors.push('smart');
  } else if (getRandomInt(0,9) == 0) {
    this.isSmart = true;
    descriptors.push('smart');
  }
  if (!this.isSmart && getRandomInt(0,9) == 0) {
    this.isDumb = true;
    descriptors.push('dumb');
  }
  if (this.race == 'elf' && getRandomInt(0,9) <= 2) {
    this.isPretty = true;
    descriptors.push('pretty');
  } else if (this.race == 'human' && getRandomInt(0,9) <= 1) {
    this.isPretty = true;
    descriptors.push('pretty');
  } else if (getRandomInt(0,9) == 0) {
    this.isPretty = true;
    descriptors.push('pretty');
  }
  if (!this.isPretty && this.race == 'gnome' && getRandomInt(0,9) <= 2) {
    this.isUgly = true;
    descriptors.push('ugly');
  } else if (!this.isPretty && this.race == 'human' && getRandomInt(0,9) <= 1) {
    this.isUgly = true;
    descriptors.push('ugly');
  } else if (!this.isPretty && getRandomInt(0,9) == 0) {
    this.isUgly = true;
    descriptors.push('ugly');
  }
  if (getRandomInt(0,9) <= 2 && descriptors.length) {
    switch(descriptors[getRandomInt(0,descriptors.length-1)]) {
      case 'strong':
         this.title = 'the ' + ucwords(strongNames[getRandomInt(0, strongNames.length-1)]);
         break;
      case 'weak':
         this.title = 'the ' + ucwords(weakNames[getRandomInt(0, weakNames.length-1)]);
         break;
      case 'smart':
        this.title = 'the ' + ucwords(smartNames[getRandomInt(0, smartNames.length-1)]);
        break;
      case 'dumb':
        this.title = 'the ' + ucwords(dumbNames[getRandomInt(0, dumbNames.length-1)]);
        break;
      case 'pretty':
        this.title = 'the ' + ucwords(prettyNames[getRandomInt(0, prettyNames.length-1)]);
        break;
      case 'ugly':
        this.title = 'the ' + ucwords(uglyNames[getRandomInt(0, uglyNames.length-1)]);
        break;
    }
  }
  if (this.isStrong || this.isWeak || this.isSmart || this.isDumb || this.isPretty || this.isUgly) {
    this.description += '<br><br>';
  }
  if (this.isStrong) {
    this.description += ucwords(this.getPronoun(this.gender) ) + ' is ' + strongAdverb() + ' strong. ';
  }
  if (this.isWeak) {
    this.description += ucwords(this.getPronoun(this.gender) ) + ' is ' + weakAdverb() + ' weak. ';
  }
  if (this.isSmart) {
    this.description += ucwords(this.getPronoun(this.gender) ) + ' is ' + smartAdverb() + ' intelligent. ';
  }
  if (this.isDumb) {
    this.description += ucwords(this.getPronoun(this.gender) ) + ' is ' + dumbAdverb() + ' stupid. ';
  }
  if (this.isPretty) {
    this.description += ucwords(this.getPronoun(this.gender) ) + ' is ' + prettyAdverb() + ' attractive. ';
  }
  if (this.isUgly) {
    this.description += ucwords(this.getPronoun(this.gender) ) + ' is ' + uglyAdverb() + ' ugly. ';
  }
  var hair = hairStyles[getRandomInt(0,hairStyles.length-1)];
  this.description += '<br><br>';
  if (hair != 'bald') {
    var hairColor = hairColors[getRandomInt(0,hairColors.length-1)];
    this.description += ucwords(this.getPronoun(this.gender)) + ' has ' + hairColor + ' hair in ' + hair + '.';
  } else {
    this.description += ucwords(this.getPronoun(this.gender)) + ' has a bald head.';
  }
  if (this.gender == 'male') {
    var hasBeard = false;
    if (getRandomInt(0,2) <= 1) {
      hasBeard = true;
      this.description += ' He has a ' + beardStyles[getRandomInt(0,beardStyles.length-1)] + ' beard';
    }
    if (getRandomInt(0,2) <= 1) {
      if (hasBeard) {
        this.description += ' and a ';
      } else {
        this.description += ' He has a ';
      }
      this.description += mustacheStyles[getRandomInt(0,mustacheStyles.length-1)] + ' mustache.';
    } else {
      if (hasBeard) {
        this.description += '.';
      }
    }
  }
};

function uglyAdverb() {
  var adverb;
  switch(getRandomInt(0,3)) {
    case 0:
      adverb = 'a little';
      break;
    case 1:
      adverb = 'quite';
      break;
    case 2:
      adverb = 'strikingly';
      break;
    case 3:
      adverb = 'horrendously';
      break;
  }
  return adverb;
}
function strongAdverb() {
  var adverb;
  switch(getRandomInt(0,3)) {
    case 0:
      adverb = 'pretty';
      break;
    case 1:
      adverb = 'very';
      break;
    case 2:
      adverb = 'powerfully';
      break;
    case 3:
      adverb = 'immensely';
      break;
  }
  return adverb;
}
function smartAdverb() {
  var adverb;
  switch(getRandomInt(0,3)) {
    case 0:
      adverb = 'fairly';
      break;
    case 1:
      adverb = 'quite';
      break;
    case 2:
      adverb = 'extremely';
      break;
    case 3:
      adverb = 'brilliantly';
      break;
  }
  return adverb;
}
function prettyAdverb() {
  var adverb;
  switch(getRandomInt(0,3)) {
    case 0:
      adverb = 'quite';
      break;
    case 1:
      adverb = 'pleasantly';
      break;
    case 2:
      adverb = 'ridiculously';
      break;
    case 3:
      adverb = 'jaw-droppingy';
      break;
  }
  return adverb;
}
function dumbAdverb() {
  var adverb;
  switch(getRandomInt(0,3)) {
    case 0:
      adverb = 'slightly';
      break;
    case 1:
      adverb = 'rather';
      break;
    case 2:
      adverb = 'pitifully';
      break;
    case 3:
      adverb = 'embarrassingly';
      break;
  }
  return adverb;
}
function weakAdverb() {
  var adverb;
  switch(getRandomInt(0,3)) {
    case 0:
      adverb = 'somewhat';
      break;
    case 1:
      adverb = 'slightly';
      break;
    case 2:
      adverb = 'pathetically';
      break;
    case 3:
      adverb = 'infirmedly';
      break;
  }
  return adverb;
}

this.getPronoun = function(text, possessive) {
  if (possessive && text == 'male') {
    return 'his';
  }
  if (possessive && text == 'female') {
    return 'her';
  }
  if (!possessive && text == 'male') {
    return 'he';
  }
  if (!possessive && text == 'female') {
    return 'she';
  }
}

this.getSkillLevel = function() {
  var skill;
  switch(getRandomInt(0,5)) {
    case 0:
      skill = 'pitiful';
      break;
    case 1:
      skill = 'poor';
      break;
    case 2:
      skill = 'mediocre';
      break;
    case 3:
      skill = 'advanced';
      break;
    case 4:
      skill = 'expert';
      break;
    case 5:
      skill = 'masterful';
      break;
  }
  return skill;
}

this.makeCharacterTraits = function() {
  if (monsterRaces.indexOf(this.race) == -1) {
    var jobProb = getRandomInt(0,99);
    if (jobProb <= 74) {
      this.job = commonTrades[getRandomInt(0,commonTrades.length-1)];
    }else if (jobProb <= 94) {
      this.job = lessCommonTrades[getRandomInt(0,lessCommonTrades.length-1)];
    } else {
      this.job = rareTrades[getRandomInt(0,rareTrades.length-1)];
    }
  } else {
    this.job = monsterJobs[getRandomInt(0,monsterJobs.length-1)];
  }
  this.jobSkill = this.getSkillLevel();
  this.trait1 =  traits[getRandomInt(0,traits.length-1)];
  this.trait2 = traits[getRandomInt(0,traits.length-1)];
}

function feminizeThat(string) {
  var flip = getRandomInt(1,3);
  switch(flip){
    case 1:
      string += "a";
      break;
    case 2:
      string += "y";
      break;
    case 3:
      string += "o";
      break;
  }
  return string;
}

this.getNumerator = function(text) {
  var firstLetter = text.charAt(0).toLowerCase();
  if (firstLetter == 'a' || firstLetter == 'e' || firstLetter == 'i' || firstLetter == 'o' || firstLetter == 'u') {
    return 'an';
  } else {
    return 'a';
  }
};
}
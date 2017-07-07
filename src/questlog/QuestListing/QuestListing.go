package QuestListing

import (
  "log"
  _ "github.com/go-sql-driver/mysql"
  "questlog/DBUtils"
  "strconv"
)

type Quest struct {
  Qid int `json:"qid"`
  Gmid int `json:"gmid"`
  Gm_name string `json:"gmname"`
  Name string `json:"name"`
  Stamp int `json:"timestamp"`
  Characters []Character `json:"players"`
  Count int `json:"count"`
  Last string `json:"last"`
  Type string `json:"type"`
}

type QuestInfo struct {
  Qid int `json:"qid"`
  Description string `json:"description"`
  Title string `json:"title"`
}
type QuestMember struct {
  Uid int `json:"uid"`
  Cid int `json:"cid"`
}
type QuestPermissions struct {
  GMid int `json:"gmid"`
  Members []QuestMember `json:"members"`
}

type Character struct {
  Cid int `json:"cid"`
  Char_name string `json:"name"`
  Uid int `json:"uid"`
}

func GetQuestPermissions(qid int) QuestPermissions {
  var permissions = QuestPermissions{}
  db := DBUtils.OpenDB();
  db.QueryRow("select uid from quests WHERE qid = ?", qid).Scan(&permissions.GMid)
  rows, err := db.Query("select qm.cid,c.uid from quest_members as qm, characters as c where qm.qid = ? and qm.cid=c.cid", qid)
  if err != nil {
    log.Fatal(err)
  }
  defer rows.Close()
  for rows.Next() {
    member := QuestMember{}
    err := rows.Scan(&member.Cid, &member.Uid)
    if err != nil {
      log.Println(err)
    }
    permissions.Members = append(permissions.Members, member)
  }
  DBUtils.CloseDB(db)
  return permissions
}

func GetQuestInfo(qid int) QuestInfo {
  var info = QuestInfo{}
  db := DBUtils.OpenDB();
  db.QueryRow("select qid,preface_text from quest_prefaces WHERE qid = ?", qid).Scan(&info.Qid, &info.Description)
  db.QueryRow("select quest_name from quests WHERE qid = ?", qid).Scan(&info.Title)
  DBUtils.CloseDB(db)
  return info
}

func GetGroupedQuests(id int) []Quest {
  db := DBUtils.OpenDB();
  var quests []Quest
  var gm_quests string = "0"
  var player_quests string = "0"
  
  // gm quests
  rows, err := db.Query("select q.qid,q.uid,u.login_name,q.quest_name,UNIX_TIMESTAMP(q.timestamp) from quests as q, users as u where q.quest_status < 4 AND q.uid = ? AND u.uid = ? ORDER BY q.timestamp DESC", id, id)
  if err != nil {
    log.Fatal(err)
  }
  defer rows.Close()
  for rows.Next() {
    quest := Quest{}
    err := rows.Scan(&quest.Qid, &quest.Gmid, &quest.Gm_name, &quest.Name, &quest.Stamp)
    if err != nil {
      log.Fatal(err)
    }
    quest.Type = "gm"
    gm_quests += "," + strconv.Itoa(quest.Qid);
    db.QueryRow("select count(*) from posts where qid = ?", quest.Qid).Scan(&quest.Count)
    var lastCID int
    var lastUID int
    db.QueryRow("select cid,uid from posts where qid = ? order by timestamp desc limit 1", quest.Qid).Scan(&lastCID, &lastUID)
    if (lastCID == 0) {
      db.QueryRow("select login_name from users where uid = ?", lastUID).Scan(&quest.Last);
    } else {
      db.QueryRow("select char_name from characters where cid = ?", lastCID).Scan(&quest.Last);
    }
    rows2, err := db.Query("select c.uid, qm.cid, c.char_name from quest_members as qm, characters as c WHERE qm.qid = ? AND c.cid=qm.cid", quest.Qid)
    if err != nil {
      log.Fatal(err)
    }
    defer rows2.Close()

    var characters []Character
    for rows2.Next() {
      character := Character{}
      err := rows2.Scan(&character.Uid, &character.Cid, &character.Char_name)
      if err != nil {
        log.Println(err)
      }
      characters = append(characters, character)
    }
    quest.Characters = characters
    if (quest.Qid > 0) {
      quests = append(quests, quest)
    }
  }
  
  // player quests
  rows, err = db.Query("select cid from characters where uid=?", id)
  defer rows.Close()
  if err != nil {
    log.Fatal(err)
  }
  var characters []Character
  for rows.Next() {
    character := Character{}
    err := rows.Scan(&character.Cid)
    if err != nil {
      log.Println(err)
    }
    characters = append(characters, character)
  }
  
  for i:=0; i < len(characters); i++ {
    rows, err := db.Query("select qid from quest_members where cid = ?", characters[i].Cid)
    if err != nil {
      log.Println(err)
    }
    defer rows.Close()
    
    for rows.Next() {
      var qid int
      rows.Scan(&qid)
      quest := Quest{}
      db.QueryRow("select q.qid,q.uid,u.login_name,q.quest_name,UNIX_TIMESTAMP(q.timestamp) from quests as q, users as u where quest_status < 4 AND q.qid = ?  AND u.uid=q.uid ORDER BY q.timestamp DESC", qid).Scan(&quest.Qid, &quest.Gmid, &quest.Gm_name, &quest.Name, &quest.Stamp)
      quest.Type = "player"   
      db.QueryRow("select count(*) from posts where qid = ?", quest.Qid).Scan(&quest.Count)
      var lastCID int
      var lastUID int
      db.QueryRow("select cid,uid from posts where qid = ? order by timestamp desc limit 1", quest.Qid).Scan(&lastCID, &lastUID)
      player_quests += "," + strconv.Itoa(lastCID);
      if (lastCID == 0) {
        db.QueryRow("select login_name from users where uid = ?", lastUID).Scan(&quest.Last);
      } else {
        db.QueryRow("select char_name from characters where cid = ?", lastCID).Scan(&quest.Last);
      }
      
      rows2, err := db.Query("select c.uid, qm.cid, c.char_name from quest_members as qm, characters as c WHERE qm.qid = ? AND c.cid=qm.cid", quest.Qid)
      if err != nil {
        log.Fatal(err)
      }
      defer rows2.Close()

      var allCharacters []Character
      for rows2.Next() {
        character := Character{}
        err := rows2.Scan(&character.Uid, &character.Cid, &character.Char_name)
        if err != nil {
          log.Println(err)
        }
        allCharacters = append(allCharacters, character)
      }
      quest.Characters = allCharacters
      if (quest.Qid > 0) {
        quests = append(quests, quest)
      }
    }
  }

  // other quests
  rows, err = db.Query("SELECT qid FROM quest_members WHERE qid NOT IN (" + gm_quests + ") AND cid NOT IN (" + player_quests + ")")
  defer rows.Close()
  if err != nil {
    log.Fatal(err)
  }
  for rows.Next() {
    var qid int
    err := rows.Scan(&qid)
    if err != nil {
      log.Fatal(err)
    }
    quest := Quest{}
    quest.Type = "other"
    db.QueryRow("select q.qid,q.uid,u.login_name,q.quest_name,UNIX_TIMESTAMP(q.timestamp) from quests as q, users as u where q.quest_status < 4 AND q.qid = ? AND u.uid=q.uid ORDER BY q.timestamp DESC", qid).Scan(&quest.Qid, &quest.Gmid, &quest.Gm_name, &quest.Name, &quest.Stamp)
    db.QueryRow("select count(*) from posts where qid = ?", quest.Qid).Scan(&quest.Count)
    var lastCID int
    var lastUID int
    db.QueryRow("select cid,uid from posts where qid = ? order by timestamp desc limit 1", quest.Qid).Scan(&lastCID, &lastUID)
    if (lastCID == 0) {
      db.QueryRow("select login_name from users where uid = ?", lastUID).Scan(&quest.Last);
    } else {
      db.QueryRow("select char_name from characters where cid = ?", lastCID).Scan(&quest.Last);
    }
    rows2, err := db.Query("select c.uid, qm.cid, c.char_name from quest_members as qm, characters as c WHERE qm.qid = ? AND c.cid=qm.cid", quest.Qid)
    if err != nil {
      log.Fatal(err)
    }
    defer rows2.Close()
    var allCharacters []Character
    for rows2.Next() {
      character := Character{}
      err := rows2.Scan(&character.Uid, &character.Cid, &character.Char_name)
      if err != nil {
        log.Println(err)
      }
      allCharacters = append(allCharacters, character)
    }
    quest.Characters = allCharacters
    if (quest.Qid > 0) {
      quests = append(quests, quest)
    }
  }
  DBUtils.CloseDB(db)
  return quests
}

func GetAllQuests() []Quest {
  db := DBUtils.OpenDB();
  rows, err := db.Query("select q.qid,q.uid,u.login_name,q.quest_name,UNIX_TIMESTAMP(q.timestamp) from quests as q, users as u where status = ? AND u.uid=q.uid ORDER BY q.timestamp ASC", 1)
  if err != nil {
    log.Fatal(err)
  }
  defer rows.Close()
  var quests []Quest
  for rows.Next() {
    quest := Quest{}
    err := rows.Scan(&quest.Qid, &quest.Gmid, &quest.Gm_name, &quest.Name, &quest.Stamp)
    if err != nil {
        log.Fatal(err)
    }
    db.QueryRow("select count(*) from posts where qid = ?", quest.Qid).Scan(&quest.Count)
    var lastCID int
    var lastUID int
    db.QueryRow("select cid,uid from posts where qid = ? order by timestamp desc limit 1", quest.Qid).Scan(&lastCID, &lastUID)
    if (lastCID == 0) {
      db.QueryRow("select login_name from users where uid = ?", lastUID).Scan(&quest.Last);
    } else {
      db.QueryRow("select char_name from characters where cid = ?", lastCID).Scan(&quest.Last);
    }
    
    rows2, err := db.Query("select c.uid, qm.cid, c.char_name from quest_members as qm, characters as c WHERE qm.qid = ? AND c.cid=qm.cid", quest.Qid)
    if err != nil {
      log.Fatal(err)
    }
    defer rows2.Close()

    var characters []Character
    for rows2.Next() {
      character := Character{}
      err := rows2.Scan(&character.Uid, &character.Cid, &character.Char_name)
      if err != nil {
        log.Println(err)
      }
      characters = append(characters, character)
    }
    quest.Characters = characters
    quests = append(quests, quest)
  }
  DBUtils.CloseDB(db)
  return quests;
}
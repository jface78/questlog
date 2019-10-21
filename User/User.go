package User

import (
  "log"
  _ "github.com/go-sql-driver/mysql"
  "jface/questlog/DBUtils"
)

type Character struct {
  Cid int `json:"cid"`
  Char_name string `json:"name"`
}

type Quest struct {
  Qid int `json:"qid"`
  Quest_name string `json:"name"`
  Characters []Character `json:"characters"`
}

type UserDetails struct {
  Uid int `json:"uid"`
  Name string `json:"name"`
  GMQuests []Quest `json:"gm_quests"`
  CharQuests []Quest `json:"character_quests"`
  LastLogged string `json:"last_login"`
}

func GetUserInfo(uid int) UserDetails {
  log.Println("Generate user details")
  db := DBUtils.OpenDB();
  details := UserDetails{};
  details.Uid = uid
  db.QueryRow("select login_name,UNIX_TIMESTAMP(timestamp) FROM users WHERE uid=?", uid).Scan(&details.Name,&details.LastLogged)

  rows, err := db.Query("select qid, quest_name from quests WHERE uid=? AND quest_status < 4", uid)
  if err != nil {
    log.Fatal(err)
  }
  defer rows.Close()
  for rows.Next() {
    quest := Quest{}
    err := rows.Scan(&quest.Qid, &quest.Quest_name)
    if err != nil {
      log.Println(err)
    }
    details.GMQuests = append(details.GMQuests, quest)
  }
  
  
  rows, err = db.Query("select cid, char_name from characters WHERE uid=?", uid)
  if err != nil {
    log.Fatal(err)
  }
  defer rows.Close()
  for rows.Next() {
    char := Character{}
    err := rows.Scan(&char.Cid, &char.Char_name)
    if err != nil {
      log.Println(err)
    }
    quest_rows, err := db.Query("select qm.qid, q.quest_name from quest_members as qm, quests as q WHERE qm.cid=? AND q.quest_status < 4 AND qm.qid = q.qid", char.Cid)
    if err != nil {
      log.Println(err)
    }
    defer quest_rows.Close()
    for quest_rows.Next() {
      quest := Quest{}
      err = quest_rows.Scan(&quest.Qid, &quest.Quest_name)
      if err != nil {
        log.Println(err)
      }
      quest.Characters = append(quest.Characters, char)
      details.CharQuests = append(details.CharQuests, quest)
    }
  }
  DBUtils.CloseDB(db)
  return details
}


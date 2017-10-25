package Character

import (
  "log"
  "github.com/jonathanface/questlog/DBUtils"
)

type Quest struct {
  Qid int `json:"qid"`
  Quest_name string `json:"name"`
}

type CharacterInfo struct {
  Cid int `json:"cid"`
  Uid int `json:"uid"`
  Char_name string `json:"name"`
  Char_title string `json:"title"`
  Stamp int `json:"timestamp"`
  Preface string `json:"preface"`
  Profile string `json:"profile"`
  Quests []Quest `json:"quests"`
}

func GetCharacterInfo(cid int) CharacterInfo {
  var info = CharacterInfo{}
  db := DBUtils.OpenDB();
  db.QueryRow("select cid,uid,char_name,char_title,UNIX_TIMESTAMP(created) from characters where cid = ?", cid).Scan(&info.Cid, &info.Uid, &info.Char_name, &info.Char_title, &info.Stamp)
  db.QueryRow("select history from character_prefaces where cid = ?", cid).Scan(&info.Preface)
  db.QueryRow("select profile from character_profiles where cid = ?", cid).Scan(&info.Profile)
  rows, err := db.Query("select q.qid, q.quest_name from quests q, quest_members qm where qm.cid=? and q.qid=qm.qid and quest_status < 4", cid);
  if err != nil {
    log.Fatal(err)
  }
  defer rows.Close()
  var quests []Quest
  for rows.Next() {
    quest := Quest{}
    rows.Scan(&quest.Qid, &quest.Quest_name)
    quests = append(quests, quest)
  }
  info.Quests = quests
  DBUtils.CloseDB(db)
  return info
}
package API

import (
	"log"
	"net/http"
)

type Character struct {
	Cid       int    `json:"cid"`
	Char_name string `json:"name"`
	Uid       int    `json:"uid"`
}

type Quest struct {
	Qid        int         `json:"qid"`
	Gmid       int         `json:"gmid"`
	Gm_name    string      `json:"gmname"`
	Name       string      `json:"name"`
	Stamp      int         `json:"timestamp"`
	Characters []Character `json:"players"`
	Count      int         `json:"count"`
	Last       string      `json:"last"`
	Type       string      `json:"type"`
}

func FetchQuests(w http.ResponseWriter, r *http.Request) {
	db := OpenDB()
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
		if lastCID == 0 {
			db.QueryRow("select login_name from users where uid = ?", lastUID).Scan(&quest.Last)
		} else {
			db.QueryRow("select char_name from characters where cid = ?", lastCID).Scan(&quest.Last)
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
	CloseDB(db)
	respondWithJson(w, http.StatusOK, quests)
	//return quests;
}

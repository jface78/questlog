package API

import (
	"log"
	"net/http"
	"strconv"

	"github.com/gorilla/mux"
)

func FetchQuest(w http.ResponseWriter, r *http.Request) {
	//qid int, start int, length int, order string
	vars := mux.Vars(r)
	qid, err := strconv.Atoi(vars["qid"])
	if err != nil || vars["qid"] == "" {
		RespondWithError(w, http.StatusBadRequest, "Missing Quest ID")
		return
	}
	startStr, ok := r.URL.Query()["start"]
	if !ok {
		RespondWithError(w, http.StatusBadRequest, "Missing Start Parameter")
		return
	}
	start, err := strconv.Atoi(startStr[0])
	if err != nil {
		RespondWithError(w, http.StatusBadRequest, "Invalid Start Parameter")
		return
	}
	lengthStr, ok := r.URL.Query()["length"]
	if !ok {
		RespondWithError(w, http.StatusBadRequest, "Missing Length Parameter")
		return
	}
	length, err := strconv.Atoi(lengthStr[0])
	if err != nil {
		RespondWithError(w, http.StatusBadRequest, "Invalid Length Parameter")
		return
	}
	orderStr, ok := r.URL.Query()["order"]
	if !ok {
		RespondWithError(w, http.StatusBadRequest, "Missing Order Parameter")
		return
	}
	order := orderStr[0]

	log.Println("vars", qid, start, length, order)

	var posts []Post
	db := OpenDB()
	rows, err := db.Query("select pid,qid,uid,cid,post_text,UNIX_TIMESTAMP(post_date) from posts WHERE qid = ? ORDER BY post_date "+order+" LIMIT ?, ?", qid, start, length)
	if err != nil {
		log.Fatal(err)
	}
	defer rows.Close()
	for rows.Next() {
		post := Post{}
		err := rows.Scan(&post.Pid, &post.Qid, &post.Uid, &post.Cid, &post.Text, &post.Stamp)
		if err != nil {
			log.Fatal(err)
		}
		if post.Cid == 0 {
			db.QueryRow("select login_name from users where uid = ?", post.Uid).Scan(&post.Poster)
			post.GmPost = true
			post.Poster += " - GM"
		} else {
			post.GmPost = false
			db.QueryRow("select char_name from characters where cid = ?", post.Cid).Scan(&post.Poster)
		}
		posts = append(posts, post)
	}
	db.Close()
	respondWithJson(w, http.StatusOK, posts)
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
	db.Close()
	respondWithJson(w, http.StatusOK, quests)
}

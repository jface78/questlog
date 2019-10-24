package API

import (
	"database/sql"
	"encoding/json"
	"log"
	"net/http"
)

const (
	API_SECRET = "cuO9w51bA&IJ"
)

type Creds struct {
	User   string `json:"dbUser"`
	Pass   string `json:"dbPass"`
	Name   string `json:"dbName"`
	Host   string `json:"dbHost"`
	Port   string `json:"dbPort"`
	PcName string `json:"pcName"`
}

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

type Post struct {
	Pid    int    `json:"pid"`
	Qid    int    `json:"qid"`
	Uid    int    `json:"uid"`
	Cid    int    `json:"cid"`
	Poster string `json:"poster"`
	Text   string `json:"text"`
	Stamp  int    `json:"stamp"`
	GmPost bool   `json:"gmPost"`
}

var db_creds Creds

func SetCredentials(creds Creds) {
	db_creds = creds
}

func OpenDB() *sql.DB {
	dbConnStr := db_creds.User + ":" + db_creds.Pass + "@tcp(" + db_creds.Host + ":" + db_creds.Port + ")/" + db_creds.Name
	db, err := sql.Open("mysql", dbConnStr)
	if err != nil {
		log.Fatal(err)
	}
	return db
}

func GetSecret() string {
	return API_SECRET
}

func RespondWithError(w http.ResponseWriter, code int, msg string) {
	respondWithJson(w, code, map[string]string{"error": msg})
}

func respondWithJson(w http.ResponseWriter, code int, payload interface{}) {
	response, _ := json.Marshal(payload)
	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(code)
	w.Write(response)
}

/*
func isUserGM(r *http.Request, qid int) bool {
	session, err := sessionStore.Get(r, QUESTLOG_SESSION_ID)
	if err != nil {
		return false
	}
	uid := session.Values["id"].(int)
	db := OpenDB()
	var gmID int
	db.QueryRow("select uid from quests where qid = ?", qid).Scan(&gmID)
	if gmID == uid {
		return true
	} else {
		return false
	}
}

func verifyWritePermission(r *http.Request, typeOf string, id int) bool {
	session, err := sessionStore.Get(r, QUESTLOG_SESSION_ID)
	if err != nil {
		return false
	}
	uid := session.Values["id"].(int)
	db := OpenDB()
	if typeOf == "create" {
		var gmID int
		db.QueryRow("select uid from quests where qid = ?", id).Scan(&gmID)
		if gmID == uid {
			CloseDB(db)
			return true
		}
		rows, err := db.Query("select cid from quest_members where qid = ?", id)
		if err != nil {
			log.Fatal(err)
		}
		defer rows.Close()
		var cid int
		var matchedCids []int
		for rows.Next() {
			err := rows.Scan(&cid)
			if err != nil {
				log.Fatal(err)
			}
			rows2, err2 := db.Query("select uid from characters where cid = ?", cid)
			if err2 != nil {
				log.Fatal(err)
			}
			defer rows2.Close()
			var cuid int
			for rows2.Next() {
				err := rows2.Scan(&cuid)
				if err != nil {
					log.Fatal(err)
				}
				if cuid == uid {
					matchedCids = append(matchedCids, cid)
				}
			}
		}
		CloseDB(db)
		if len(matchedCids) > 0 {
			return true
		} else {
			return false
		}
	} else {
		var posterID int
		db.QueryRow("select uid from posts where pid = ?", id).Scan(&posterID)
		CloseDB(db)
		log.Println(posterID)
		log.Println(uid)
		if posterID == uid {
			return true
		} else {
			return false
		}
	}
}

var sessionStore = sessions.NewCookieStore([]byte(QUESTLOG_SESSION_ID))

func hashPass(user string, pass string) string {
	hash := ripemd160.New()
	hash.Write([]byte(pass + user))
	hexed := hex.EncodeToString(hash.Sum(nil))
	hash = ripemd160.New()
	hash.Write([]byte(pass + hexed))
	return hex.EncodeToString(hash.Sum(nil))
}

func setSession(w http.ResponseWriter, r *http.Request, userID int, username string, timestamp int) {
	session, err := sessionStore.Get(r, QUESTLOG_SESSION_ID)
	if err != nil {
		serverError(w, err.Error())
		return
	}
	session.Values["id"] = userID
	session.Values["name"] = username
	session.Values["timestamp"] = timestamp
	session.Values["ip"] = r.RemoteAddr
	session.Save(r, w)
}

func checkSession(w http.ResponseWriter, r *http.Request) {
	session, err := sessionStore.Get(r, QUESTLOG_SESSION_ID)
	if err != nil {
		serverError(w, err.Error())
		return
	}
	if len(session.Values) > 0 {
		entry := make(map[string]interface{})
		entry["uid"] = session.Values["id"]
		entry["login_name"] = session.Values["name"]
		entry["timestamp"] = session.Values["timestamp"]
		entry["ip"] = session.Values["ip"]
		jsonData, err := json.Marshal(entry)
		if err != nil {
			serverError(w, err.Error())
			return
		}
		w.WriteHeader(http.StatusOK)
		fmt.Fprintf(w, string(jsonData))
	} else {
		fileNotFound(w, "session is empty")
		return
	}
}

func login(w http.ResponseWriter, r *http.Request) {
	r.ParseForm()
	user := r.Form["user"][0]
	pass := r.Form["pass"][0]
	hash := hashPass(user, pass)
	db := OpenDB()
	rows, err := db.Query("select u.uid,u.login_name,ul.date,ul.ip from users as u, user_logins as ul where u.login_hash = ? and ul.uid = u.uid ORDER BY DATE asc LIMIT 1", hash)
	if err != nil {
		serverError(w, err.Error())
		return
	}
	defer rows.Close()

	login := LoginModel{}
	for rows.Next() {
		err := rows.Scan(&login.Uid, &login.Name, &login.Stamp, &login.Ip)
		if err != nil {
			serverError(w, err.Error())
			return
		}
	}

	address := r.RemoteAddr
	stmt, err := db.Prepare("update user_logins set date=UNIX_TIMESTAMP(now()),ip = ? where uid=?")
	if err != nil {
		log.Println("can't update login")
	}
	defer stmt.Close()
	_, err = stmt.Exec(address, login.Uid)
	if err != nil {
		log.Println("can't update login")
	}
	jsonData, err := json.Marshal(login)
	CloseDB(db)
	setSession(w, r, login.Uid, login.Name, login.Stamp)
	w.WriteHeader(http.StatusOK)
	fmt.Fprintf(w, string(jsonData))
}

func logout(w http.ResponseWriter, r *http.Request) {
	session, err := sessionStore.Get(r, QUESTLOG_SESSION_ID)
	if err != nil {
		fileNotFound(w, err.Error())
		return
	}
	delete(session.Values, QUESTLOG_SESSION_ID)
	session.Options.MaxAge = -1
	_ = session.Save(r, w)
	writeSuccess(w)
}


func handleLogin(w http.ResponseWriter, r *http.Request) {
	log.Println("handle login")
	r.ParseForm()
	user := r.Form["user"][0]
	pass := r.Form["pass"][0]
	if len(user) == 0 || len(pass) == 0 {
		badRequest(w, "missing username or pass")
		return
	} else {
		login(w, r)
	}
}
*/

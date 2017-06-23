package main

import (
    "fmt"
    "net/http"
    "github.com/gorilla/mux"
    "github.com/gorilla/sessions"
    "github.com/gorilla/context"
    "log"
    "database/sql"
    _ "github.com/go-sql-driver/mysql"
    "encoding/json"
    "golang.org/x/crypto/ripemd160"
    "encoding/hex"
    "strconv"
)

const (
  DB_USER     = "questlog"
  DB_PASSWORD = "Z6c2a2yyb2vG"
  DB_NAME     = "questlog"
  DB_HOST     = "tcp(52.4.79.128:3306)"
  SERVICE_PATH = "/service"
)


const QUESTLOG_SESSION_ID = "questlog"
const PORT = ":1337"
var sessionStore = sessions.NewCookieStore([]byte(QUESTLOG_SESSION_ID))

func openDB() *sql.DB {
  var connectStr = DB_USER + ":" + DB_PASSWORD + "@" + DB_HOST + "/" + DB_NAME;
  db, err := sql.Open("mysql", connectStr)
  if err != nil {
    log.Fatal(err)
  }
  
  err = db.Ping()
  if err != nil {
    log.Fatal("DB not ready")
  }
  return db;
}

func closeDB(db *sql.DB) {
  defer db.Close()
}

type Character struct {
  Cid int `json:"cid"`
  Char_name string `json:"name"`
  Uid int `json:"uid"`
}

type Quest struct {
  Qid int `json:"qid"`
  Gmid int `json:"gmid"`
  Gm_name string `json:"gmname"`
  Name string `json:"name"`
  Stamp int `json:"timestamp"`
  Characters []Character `json:"players"`
  Count int `json:"count"`
  Last string `json:"last"`
}

type QuestInfo struct {
  Qid int `json:"qid"`
  Description string `json:"name"`
}

func getQuestInfo(w http.ResponseWriter, qid int) {
  var info = QuestInfo{}
  db := openDB();
  db.QueryRow("select qid,preface_text from quest_prefaces WHERE qid = ?", qid).Scan(&info.Qid, &info.Description)
  jsonData, err := json.Marshal(info)
  if err != nil {
      log.Fatal(err)
  }
  closeDB(db)
  fmt.Fprintf(w, string(jsonData))
}

func getAllQuests(w http.ResponseWriter) {
  db := openDB();
  rows, err := db.Query("select q.qid,q.uid,u.login_name,q.quest_name,UNIX_TIMESTAMP(q.timestamp) from quests as q, users as u where status = ? AND u.uid=q.uid", 1)
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
        fmt.Println(err)
      }
      characters = append(characters, character)
    }
    quest.Characters = characters
    quests = append(quests, quest)
  }
  jsonData, err := json.Marshal(quests)
  if err != nil {
      log.Fatal(err)
  }
  closeDB(db)
  fmt.Fprintf(w, string(jsonData))
}

func hashPass(user string, pass string) string {
  hash := ripemd160.New()
  hash.Write([]byte(pass + user))
  hexed := hex.EncodeToString(hash.Sum(nil))
  hash = ripemd160.New()
  hash.Write([]byte(pass + hexed))
  return hex.EncodeToString(hash.Sum(nil))
}

func setSession(w http.ResponseWriter, r *http.Request, userID int, username string, timestamp int) {
  session, err := sessionStore.Get(r, "questlog-user")
  if err != nil {
    http.Error(w, err.Error(), http.StatusInternalServerError)
    return
  }
  session.Values["id"] = userID
  session.Values["name"] = username
  session.Values["timestamp"] = timestamp
  session.Save(r, w)
}

func checkSession(w http.ResponseWriter, r *http.Request) {
  session, err := sessionStore.Get(r, "questlog-user")
  if err != nil {
    http.Error(w, err.Error(), http.StatusInternalServerError)
    return
  }
  if len(session.Values) > 0 {
    entry := make(map[string]interface{})
    entry["uid"] = session.Values["id"];
    entry["login_name"] = session.Values["name"]
    entry["timestamp"] = session.Values["timestamp"]
    jsonData, err := json.Marshal(entry)
    if err != nil {
      http.Error(w, err.Error(), http.StatusInternalServerError)
      return
    }
    w.WriteHeader(http.StatusOK)
    fmt.Fprintf(w, string(jsonData))
  } else {
    w.WriteHeader(http.StatusNotFound)
  }
}

type LoginModel struct {
  Uid int `json:"uid"`
  Name string `json:"name"`
  Stamp int `json:"last_login_time"`
}

func login(w http.ResponseWriter, r *http.Request) {
  r.ParseForm()
  user := r.Form["user"][0]
  pass := r.Form["pass"][0]
  hash := hashPass(user, pass)
  db := openDB()
  rows, err := db.Query("select uid,login_name,UNIX_TIMESTAMP(timestamp) from users where login_hash = ?", hash)
  if err != nil {
    log.Fatal(err)
  }
  defer rows.Close()
  login := LoginModel{}
  for rows.Next() {
    err := rows.Scan(&login.Uid, &login.Name, &login.Stamp)
    if err != nil {
      log.Fatal(err)
    }
  }
  
  jsonData, err := json.Marshal(login)
  closeDB(db) 
  setSession(w, r, login.Uid, login.Name, login.Stamp)
  w.WriteHeader(http.StatusOK)
  fmt.Fprintf(w, string(jsonData))
}

func logout(w http.ResponseWriter, r *http.Request) {
  session, err := sessionStore.Get(r, "questlog-user")
  if err != nil {
    http.Error(w, err.Error(), http.StatusInternalServerError)
    return
  }
  delete(session.Values, "sessionId")
  session.Options.MaxAge = -1
  _ = session.Save(r, w)
  w.WriteHeader(http.StatusOK)
}

func handleQuests(w http.ResponseWriter, r *http.Request) {
  log.Println("get quests")
  getAllQuests(w)
}

func handleQuestInfo(w http.ResponseWriter, r *http.Request) {
  log.Println("get quest info")
  qid, err := strconv.Atoi(mux.Vars(r)["[a-z0-9]+"])
  if (err != nil) {
    log.Fatal(err)
  }
  getQuestInfo(w, qid)
}

func handleSessionCheck(w http.ResponseWriter, r *http.Request) {
  log.Println("handle session")
  checkSession(w, r)
}

func handleLogin(w http.ResponseWriter, r *http.Request) {
  log.Println("handle login")
  login(w, r)
}
func handleLogout(w http.ResponseWriter, r *http.Request) {
  log.Println("handle logout")
  logout(w, r)
}


func main() {
  rtr := mux.NewRouter()
  rtr.HandleFunc(SERVICE_PATH + "/quest/{[a-z0-9]+}/info", handleQuestInfo).Methods("GET")
  rtr.HandleFunc(SERVICE_PATH + "/quests", handleQuests).Methods("GET")
  rtr.HandleFunc(SERVICE_PATH + "/login", handleLogin).Methods("POST")
  rtr.HandleFunc(SERVICE_PATH + "/logout", handleLogout).Methods("GET")
  rtr.HandleFunc(SERVICE_PATH + "/checkSession", handleSessionCheck).Methods("GET")
  
  http.Handle("/", http.FileServer(http.Dir("./static/")))
  http.Handle("/service/", rtr);

  http.ListenAndServe(PORT, context.ClearHandler(http.DefaultServeMux))
}
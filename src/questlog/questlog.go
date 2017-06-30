package main

import (
    "fmt"
    "net/http"
    "github.com/gorilla/mux"
    "github.com/gorilla/sessions"
    "github.com/gorilla/context"
    "log"
    _ "github.com/go-sql-driver/mysql"
    "golang.org/x/crypto/ripemd160"
    "encoding/hex"
    "strconv"
    "questlog/QuestListing"
    "questlog/Posts"
    "questlog/DBUtils"
    "encoding/json"
)

const (
  SERVICE_PATH = "/service"
  QUESTLOG_SESSION_ID = "questlog-user"
  PORT = ":1337"
  DEFAULT_QUEST_PAGE_LENGTH = 50
)

type LoginModel struct {
  Uid int `json:"uid"`
  Name string `json:"name"`
  Stamp int `json:"last_login_time"`
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
    http.Error(w, err.Error(), http.StatusInternalServerError)
    return
  }
  session.Values["id"] = userID
  session.Values["name"] = username
  session.Values["timestamp"] = timestamp
  session.Save(r, w)
}

func checkSession(w http.ResponseWriter, r *http.Request) {
  session, err := sessionStore.Get(r, QUESTLOG_SESSION_ID)
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

func login(w http.ResponseWriter, r *http.Request) {
  r.ParseForm()
  user := r.Form["user"][0]
  pass := r.Form["pass"][0]
  hash := hashPass(user, pass)
  db := DBUtils.OpenDB()
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
  DBUtils.CloseDB(db) 
  setSession(w, r, login.Uid, login.Name, login.Stamp)
  w.WriteHeader(http.StatusOK)
  fmt.Fprintf(w, string(jsonData))
}

func logout(w http.ResponseWriter, r *http.Request) {
  session, err := sessionStore.Get(r, QUESTLOG_SESSION_ID)
  if err != nil {
    http.Error(w, err.Error(), http.StatusInternalServerError)
    return
  }
  delete(session.Values, QUESTLOG_SESSION_ID)
  session.Options.MaxAge = -1
  _ = session.Save(r, w)
  w.WriteHeader(http.StatusOK)
}

func handleQuests(w http.ResponseWriter, r *http.Request) {
  log.Println("get quests")
  session, err := sessionStore.Get(r, QUESTLOG_SESSION_ID)
  var jsonData [] byte
  if err != nil {
    log.Println(err)
  } else if len(session.Values) > 0 {
    jsonData, err = json.Marshal(QuestListing.GetGroupedQuests(session.Values["id"].(int)))
  } else {
    jsonData, err = json.Marshal(QuestListing.GetAllQuests())
  }
  if (err != nil) {
    log.Fatal(err)
  }
  fmt.Fprintf(w, string(jsonData))
}

func handleQuest(w http.ResponseWriter, r *http.Request) {
  log.Println("get quest")
  qid, err := strconv.Atoi(mux.Vars(r)["[0-9]+"])
  if (err != nil) {
    log.Fatal(err)
  }
  start, err := strconv.Atoi(r.URL.Query()["start"][0])
  if (err != nil) {
    log.Fatal(err)
  }
  length, err := strconv.Atoi(r.URL.Query()["length"][0])
  if (err != nil) {
    length = DEFAULT_QUEST_PAGE_LENGTH
    log.Println(err)
  }
  order := r.URL.Query().Get("order")
  if len(order) == 0 {
    order = "DESC"
  }
  log.Println(order)
  log.Println(start)
  log.Println(length)
  
  jsonData, err := json.Marshal(Posts.GetPosts(w, qid, start, length, order))
  if (err != nil) {
    log.Fatal(err)
  }
  fmt.Fprintf(w, string(jsonData))
}

func handleQuestInfo(w http.ResponseWriter, r *http.Request) {
  log.Println("get quest info")
  qid, err := strconv.Atoi(mux.Vars(r)["[0-9]+"])
  if (err != nil) {
    log.Fatal(err)
  }
  var info = QuestListing.GetQuestInfo(qid)
  if len(info.Description) == 0 {
    w.WriteHeader(http.StatusNotFound)
    w.Write([]byte("404 - File Not Found"))
    return
  }
  jsonData, err := json.Marshal(info)
  if (err != nil) {
    log.Fatal(err)
  }
  fmt.Fprintf(w, string(jsonData))
}

func handleSessionCheck(w http.ResponseWriter, r *http.Request) {
  log.Println("handle session")
  checkSession(w, r)
}

func handleLogin(w http.ResponseWriter, r *http.Request) {
  log.Println("handle login")
  r.ParseForm()
  user := r.Form["user"][0]
  pass := r.Form["pass"][0]
  if len(user) == 0 || len(pass) == 0 {
    w.WriteHeader(http.StatusBadRequest)
    w.Write([]byte("400 - Bad Request"))
    return
  } else {
    login(w, r)
  } 
}
func handleLogout(w http.ResponseWriter, r *http.Request) {
  log.Println("handle logout")
  logout(w, r)
}

func handleViewQuest(w http.ResponseWriter, r *http.Request) {
  log.Println("handle view quest")
  qid, err := strconv.Atoi(mux.Vars(r)["[0-9]+"])
  if (err != nil) {
    log.Fatal(err)
  }
  log.Println(qid)
  db := DBUtils.OpenDB();
  var count int
  db.QueryRow("select count(qid) FROM quests WHERE qid = ?", qid).Scan(&count)
  log.Println(count)
  if count > 0 {
    http.ServeFile(w, r, "./static/")
  } else {
    log.Println("Not found")
    w.WriteHeader(http.StatusNotFound)
  }
}


func main() {
  rtr := mux.NewRouter()
  rtr.HandleFunc(SERVICE_PATH + "/quests", handleQuests).Methods("GET")
  rtr.HandleFunc(SERVICE_PATH + "/quest/{[0-9]+}", handleQuest).Methods("GET")
  rtr.HandleFunc(SERVICE_PATH + "/quest/{[0-9]+}/info", handleQuestInfo).Methods("GET")
  rtr.HandleFunc(SERVICE_PATH + "/login", handleLogin).Methods("POST")
  rtr.HandleFunc(SERVICE_PATH + "/logout", handleLogout).Methods("GET")
  rtr.HandleFunc(SERVICE_PATH + "/checkSession", handleSessionCheck).Methods("GET")
  rtr.HandleFunc("/quest/{[0-9]+}/", handleViewQuest).Methods("GET")
  rtr.PathPrefix("/").Handler(http.FileServer(http.Dir("./static/")))
  
  http.Handle("/", rtr)
  http.ListenAndServe(PORT, context.ClearHandler(http.DefaultServeMux))
}
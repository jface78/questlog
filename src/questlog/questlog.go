package main

import (
    "fmt"
    "net/http"
    "github.com/gorilla/mux"
    "github.com/gorilla/sessions"
    "github.com/gorilla/context"
    "github.com/kennygrant/sanitize"
    "log"
    _ "github.com/go-sql-driver/mysql"
    "golang.org/x/crypto/ripemd160"
    "encoding/hex"
    "strconv"
    "questlog/QuestListing"
    "questlog/Posts"
    "questlog/DBUtils"
    "questlog/Character"
    "encoding/json"
    "strings"
    "regexp"
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
  Ip string `json:"ip"`
}


func sanitizeTextForDB(input string) string {
  input = sanitize.HTML(input)
  var re = regexp.MustCompile(`\[b\](.+)?\[\/b\]`)
  input = re.ReplaceAllString(input, `<b>$1</b>`)
  re = regexp.MustCompile(`\[i\](.+)?\[\/i\]`)
  input = re.ReplaceAllString(input, `<i>$1</i>`)
  re = regexp.MustCompile(`\[u\](.+)?\[\/u\]`)
  input = re.ReplaceAllString(input, `<u>$1</u>`)
  return input
}

func isUserGM(r *http.Request, qid int) bool {
  session, err := sessionStore.Get(r, QUESTLOG_SESSION_ID)
  if err != nil {
    return false
  }
  uid := session.Values["id"].(int)
  db := DBUtils.OpenDB()
  var gmID int
  db.QueryRow("select uid from quests where qid = ?", qid).Scan(&gmID)
  if gmID == uid {
    return true
  } else {
    return false
  }
}

func verifyWritePermission (r *http.Request, typeOf string, id int) bool {
  session, err := sessionStore.Get(r, QUESTLOG_SESSION_ID)
  if err != nil {
    return false
  }
  uid := session.Values["id"].(int)
  db := DBUtils.OpenDB()
  if typeOf == "create" {
    var gmID int
    db.QueryRow("select uid from quests where qid = ?", id).Scan(&gmID)
    if gmID == uid {
      DBUtils.CloseDB(db)
      return true;
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
    DBUtils.CloseDB(db)
    if len(matchedCids) > 0 {
      return true
    } else {
      return false
    }
  } else {
    var posterID int
    db.QueryRow("select uid from posts where pid = ?", id).Scan(&posterID)
    DBUtils.CloseDB(db)
    log.Println(posterID)
    log.Println(uid)
    if posterID == uid {
      return true
    } else {
      return false
    }
  }
}

func getIPAddress(r *http.Request) string {
    var ipAddress string
    for _, h := range []string{"X-Forwarded-For", "X-Real-Ip"} {
        for _, ip := range strings.Split(r.Header.Get(h), ",") {
            // header can contain spaces too, strip those out.
            //realIP := net.ParseIP(strings.Replace(ip, " ", "", -1))
            ipAddress = ip
        }
    }
    return ipAddress
}

func forbidden (w http.ResponseWriter, err string) {
  http.Error(w, err, http.StatusForbidden)
}

func serverError (w http.ResponseWriter, err string) {
  http.Error(w, err, http.StatusInternalServerError)
}

func fileNotFound (w http.ResponseWriter, err string) {
  http.Error(w, err, http.StatusNotFound)
}

func badRequest (w http.ResponseWriter, err string) {
  http.Error(w, err, http.StatusBadRequest)
}

func writeSuccess(w http.ResponseWriter) {
  w.WriteHeader(http.StatusOK)
  w.Write([]byte("200 - Success"))
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
    entry["uid"] = session.Values["id"];
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
  db := DBUtils.OpenDB()
  rows, err := db.Query("select u.uid,u.login_name,ul.date,ul.ip from users as u, user_logins as ul where u.login_hash = ? and ul.uid = u.uid ORDER BY DATE asc LIMIT 1", hash)
  if err != nil {
    serverError(w, err.Error())
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
  log.Println(r.Header.Get("x-forwarded-for"))
  log.Println(address)
  stmt, err := db.Prepare("update user_logins set date=UNIX_TIMESTAMP(now()),ip = ? where uid=?")
  if (err != nil) {
    log.Println("can't update login")
  }
  defer stmt.Close()
  _, err = stmt.Exec(address, login.Uid)
  if (err != nil) {
    log.Println("can't update login")
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
    fileNotFound(w, err.Error())
    return
  }
  delete(session.Values, QUESTLOG_SESSION_ID)
  session.Options.MaxAge = -1
  _ = session.Save(r, w)
  writeSuccess(w)
}

func handleQuests(w http.ResponseWriter, r *http.Request) {
  log.Println("get quests")
  session, err := sessionStore.Get(r, QUESTLOG_SESSION_ID)
  var jsonData [] byte
  if err != nil {
    serverError(w, err.Error())
    return
  } else if len(session.Values) > 0 {
    jsonData, err = json.Marshal(QuestListing.GetGroupedQuests(session.Values["id"].(int)))
  } else {
    jsonData, err = json.Marshal(QuestListing.GetAllQuests())
  }
  if (err != nil) {
    serverError(w, err.Error())
  }
  fmt.Fprintf(w, string(jsonData))
}

func handlePostPermissions(w http.ResponseWriter, r *http.Request) {
  log.Println("get post permissions")
  qid, err := strconv.Atoi(mux.Vars(r)["[0-9]+"])
  if (err != nil) {
    badRequest(w, err.Error())
  }
  session, err := sessionStore.Get(r, QUESTLOG_SESSION_ID)
  if err != nil {
    serverError(w, err.Error())
    return
  }
  jsonData, err := json.Marshal(Posts.GetPostPermissions(qid, session.Values["id"].(int)))
  if (err != nil) {
    serverError(w, err.Error())
  }
  fmt.Fprintf(w, string(jsonData))
}

func handleQuestPermissions(w http.ResponseWriter, r *http.Request) {
  log.Println("get quest permission")
  qid, err := strconv.Atoi(mux.Vars(r)["[0-9]+"])
  if (err != nil) {
    badRequest(w, err.Error())
  }
  var permission = QuestListing.GetQuestPermissions(qid)
  if permission.GMid == 0 {
    fileNotFound(w, "No gm permission")
    return
  }
  jsonData, err := json.Marshal(permission)
  if (err != nil) {
    serverError(w, err.Error())
    return
  }
  fmt.Fprintf(w, string(jsonData))
}

func handleQuest(w http.ResponseWriter, r *http.Request) {
  log.Println("get quest")
  qid, err := strconv.Atoi(mux.Vars(r)["[0-9]+"])
  if (err != nil) {
    badRequest(w, err.Error())
    return
  }
  start, err := strconv.Atoi(r.URL.Query()["start"][0])
  if (err != nil) {
    badRequest(w, err.Error())
    return
  }
  length, err := strconv.Atoi(r.URL.Query()["length"][0])
  if (err != nil) {
    length = DEFAULT_QUEST_PAGE_LENGTH
    badRequest(w, err.Error())
    return
  }
  db := DBUtils.OpenDB();
  var status int
  db.QueryRow("select quest_status from quests where qid = ?").Scan(&status)
  DBUtils.CloseDB(db)
  log.Println(status)
  if status >= 4 {
    forbidden(w, err.Error())
    return
  }
  order := r.URL.Query().Get("order")
  if len(order) == 0 {
    order = "DESC"
  }
  
  jsonData, err := json.Marshal(Posts.GetPosts(qid, start, length, order))
  if (err != nil) {
    log.Fatal(err)
    serverError(w, err.Error())
    return
  }
  fmt.Fprintf(w, string(jsonData))
}

func handleQuestInfo(w http.ResponseWriter, r *http.Request) {
  log.Println("get quest info")
  qid, err := strconv.Atoi(mux.Vars(r)["[0-9]+"])
  if (err != nil) {
    log.Fatal(err)
    badRequest(w, err.Error())
    return
  }
  var info = QuestListing.GetQuestInfo(qid)
  if len(info.Description) == 0 {
    fileNotFound(w, "No description")
    return
  }
  jsonData, err := json.Marshal(info)
  if (err != nil) {
    serverError(w, err.Error())
    return
  }
  fmt.Fprintf(w, string(jsonData))
}

func handlePostEdit(w http.ResponseWriter, r *http.Request) {
  log.Println("put post edit")
  pid, err := strconv.Atoi(mux.Vars(r)["[0-9]+"])
  if (err != nil) {
    badRequest(w, err.Error())
    return
  }
  if (!verifyWritePermission(r, "edit", pid)) {
    forbidden(w, "Forbidden")
    return
  }
  r.ParseForm()
  text := r.Form["text"][0]
  log.Println(text);
  success := Posts.EditPost(pid, text)
  if (success) {
    writeSuccess(w)
  } else {
    serverError(w, "server error")
    return
  }
  return
}

func handleQuestDelete(w http.ResponseWriter, r *http.Request) {
  log.Println("delete quest")
  qid, err := strconv.Atoi(mux.Vars(r)["[0-9]+"])
  if (err != nil) {
    badRequest(w, err.Error())
    return
  }
  if !isUserGM(r, qid) {
    forbidden(w, err.Error())
    return
  } else {
    success := QuestListing.DeleteQuest(qid)
    if (success) {
      writeSuccess(w)
    } else {
      serverError(w, err.Error())
    }
  }
}

func handlePostDelete(w http.ResponseWriter, r *http.Request) {
  log.Println("delete post")
  pid, err := strconv.Atoi(mux.Vars(r)["[0-9]+"])
  if (err != nil) {
    badRequest(w, err.Error())
    return
  }
  r.ParseForm()
  if (!verifyWritePermission(r, "delete", pid)) {
    forbidden(w, "Forbidden")
    return
  }
  success := Posts.DeletePost(pid)
  if (success) {
    writeSuccess(w)
  } else {
    serverError(w, err.Error())
  }
}

func handleNewPost(w http.ResponseWriter, r *http.Request) {
  log.Println("post create")
  qid, err := strconv.Atoi(mux.Vars(r)["[0-9]+"])
  if (err != nil) {
    badRequest(w, err.Error())
    return
  }
  r.ParseForm()
  uid, err := strconv.Atoi(r.Form["uid"][0])
  if (err != nil) {
    badRequest(w, err.Error())
    return
  }
  cid, err := strconv.Atoi(r.Form["cid"][0])
  if (err != nil) {
    badRequest(w, err.Error())
    return
  }
  if (!verifyWritePermission(r, "create", qid)) {
    forbidden(w, "Forbidden")
    return
  }
  text := sanitizeTextForDB(r.Form["text"][0])
  jsonData, err := json.Marshal(Posts.CreatePost(qid, uid, cid, text))
  if err != nil {
    serverError(w, err.Error())
    return
  }
  fmt.Fprintf(w, string(jsonData))
}

func handleCharacterInfo(w http.ResponseWriter, r *http.Request) {
  log.Println("get character info")
  cid, err := strconv.Atoi(mux.Vars(r)["[0-9]+"])
  if (err != nil) {
    badRequest(w, err.Error())
    return
  }
  var info = Character.GetCharacterInfo(cid)
  jsonData, err := json.Marshal(info)
  if (err != nil) {
    serverError(w, err.Error())
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
    badRequest(w, "missing username or pass")
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
    badRequest(w, err.Error())
  }
  db := DBUtils.OpenDB();
  var count int
  db.QueryRow("select count(qid) FROM quests WHERE qid = ? and quest_status < 4", qid).Scan(&count)
  if count > 0 {
    http.ServeFile(w, r, "./static/")
  } else {
    fileNotFound(w, "No such quest")
  }
}


func main() {
  rtr := mux.NewRouter()
  rtr.HandleFunc(SERVICE_PATH + "/quests", handleQuests).Methods("GET")
  rtr.HandleFunc(SERVICE_PATH + "/quest/{[0-9]+}", handleQuest).Methods("GET")
  rtr.HandleFunc(SERVICE_PATH + "/quest/{[0-9]+}/info", handleQuestInfo).Methods("GET")
  rtr.HandleFunc(SERVICE_PATH + "/quest/{[0-9]+}/permissions", handleQuestPermissions).Methods("GET")
  rtr.HandleFunc(SERVICE_PATH + "/quest/{[0-9]+}/delete", handleQuestDelete).Methods("DELETE")
  rtr.HandleFunc(SERVICE_PATH + "/login", handleLogin).Methods("POST")
  rtr.HandleFunc(SERVICE_PATH + "/logout", handleLogout).Methods("GET")
  rtr.HandleFunc(SERVICE_PATH + "/checkSession", handleSessionCheck).Methods("GET")
  rtr.HandleFunc(SERVICE_PATH + "/character/{[0-9]+}", handleCharacterInfo).Methods("GET")
  rtr.HandleFunc(SERVICE_PATH + "/quest/{[0-9]+}/post", handleNewPost).Methods("POST")
  rtr.HandleFunc(SERVICE_PATH + "/post/{[0-9]+}/permissions", handlePostPermissions).Methods("GET")
  rtr.HandleFunc(SERVICE_PATH + "/post/{[0-9]+}/edit", handlePostEdit).Methods("PUT")
  rtr.HandleFunc(SERVICE_PATH + "/post/{[0-9]+}/delete", handlePostDelete).Methods("DELETE")
  
  rtr.HandleFunc("/quest/{[0-9]+}/", handleViewQuest).Methods("GET")
  rtr.PathPrefix("/").Handler(http.StripPrefix("/", http.FileServer(http.Dir("static/")))) 
  
  http.Handle("/", rtr)
  http.ListenAndServe(PORT, context.ClearHandler(http.DefaultServeMux))
  log.Println("listening on " + PORT)
}
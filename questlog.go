package main

import (
    "fmt"
    "net/http"
    "github.com/gorilla/sessions"
    "github.com/gorilla/context"
    "log"
    "database/sql"
    _ "github.com/go-sql-driver/mysql"
    "encoding/json"
    "golang.org/x/crypto/ripemd160"
    "encoding/hex"

)

const (
  DB_USER     = "questlog"
  DB_PASSWORD = "Z6c2a2yyb2vG"
  DB_NAME     = "questlog"
  DB_HOST     = "tcp(52.4.79.128:3306)"
  //DB_HOST     = 'localhost'
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

func table2Map(rows *sql.Rows) interface{} {
  columns, err := rows.Columns()
  if err != nil {
    log.Fatal(err)
  }
  count := len(columns)
  tableData := make([]map[string]interface{}, 0)
  values := make([]interface{}, count)
  valuePtrs := make([]interface{}, count)
  for rows.Next() {
    for i := 0; i < count; i++ {
      valuePtrs[i] = &values[i]
    }
    rows.Scan(valuePtrs...)
    entry := make(map[string]interface{})
    for i, col := range columns {
      var v interface{}
      val := values[i]
      b, ok := val.([]byte)
      if ok {
        v = string(b)
      } else {
        v = val
      }
      entry[col] = v
    }
    tableData = append(tableData, entry)
  }
  return tableData
}

func getQuests(w http.ResponseWriter) {
  db := openDB();
  rows, err := db.Query("select quest_name from quests where status = ?", 1)
  if err != nil {
    log.Fatal(err)
  }
  defer rows.Close()
  tableData := table2Map(rows)
  jsonData, err := json.Marshal(tableData)
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

func setSession(w http.ResponseWriter, r *http.Request, userID string, username string, timestamp string) {
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

func login(w http.ResponseWriter, r *http.Request) {
  r.ParseForm()
  user := r.Form["user"][0]
  pass := r.Form["pass"][0]
  hash := hashPass(user, pass)
  db := openDB()
  rows, err := db.Query("select uid,login_name,timestamp from users where login_hash = ?", hash)
  if err != nil {
    log.Fatal(err)
  }

  var (
    uid string
    login_name string
    timestamp string
  )
  for rows.Next() {
    err := rows.Scan(&uid, &login_name, &timestamp)
    if err != nil {
      log.Fatal(err)
    }
  }
  rows, err = db.Query("select uid,login_name,timestamp from users where login_hash = ?", hash)
  if err != nil {
    log.Fatal(err)
  }
  tableData := table2Map(rows)
  defer rows.Close()
  jsonData, err := json.Marshal(tableData)
  closeDB(db) 
  setSession(w, r, uid, login_name, timestamp)
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
  getQuests(w)
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
  http.Handle("/", http.FileServer(http.Dir("./static")))
  http.HandleFunc("questlog.net", handleQuests)
  http.HandleFunc("/quests", handleQuests)
  http.HandleFunc("/login", handleLogin)
  http.HandleFunc("/logout", handleLogout)
  http.HandleFunc("/checkSession", handleSessionCheck)
  http.ListenAndServe(PORT, context.ClearHandler(http.DefaultServeMux))
}
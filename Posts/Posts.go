package Posts

import (
  "log"
  _ "github.com/go-sql-driver/mysql"
  "bitbucket.org/holodog/questlog/DBUtils"
)

type Post struct {
  Pid int64 `json:"pid"`
  Qid int64 `json:"qid"`
  Uid int64 `json:"uid"`
  Cid int64 `json:"cid"`
  Poster string `json:"poster"`
  Text string `json:"text"`
  Stamp int `json:"stamp"`
  GmPost bool `json:"gmPost"`
}

type Character struct {
  Cid int `json:"cid"`
  Char_name string `json:"name"`
  Uid int `json:"uid"`
}

type PostPermissions struct {
  Characters []Character `json:"characters"`
  Gm bool `json:"gm"`
}

func EditPost(pid int, text string) bool {
  db := DBUtils.OpenDB();
  stmt, err := db.Prepare("update posts set post_text=? where pid=?")
  if (err != nil) {
    return false
  }
  defer stmt.Close()
  _, err = stmt.Exec(text, pid)
  if (err != nil) {
    return false
  }
  DBUtils.CloseDB(db)
  return true
}

func DeletePost(pid int) bool {
  db := DBUtils.OpenDB();
  stmt, err := db.Prepare("delete from posts where pid=?")
  if (err != nil) {
    return false
  }
  defer stmt.Close()
  _, err = stmt.Exec(pid)
  if (err != nil) {
    return false
  }
  stmt, err = db.Prepare("delete from rolls where pid=?")
  if (err != nil) {
    log.Println("error deleting roll for post")
  }
  defer stmt.Close()
  _, err = stmt.Exec(pid)
  if (err != nil) {
    log.Println("error deleting roll for post")
  }
  DBUtils.CloseDB(db)
  return true
}

func GetPostPermissions(qid int, uid int) PostPermissions {
  db := DBUtils.OpenDB();
  permissions := PostPermissions{}

  db.QueryRow("select if (count(qid), 'true', 'false') from quests where qid=? and uid=?", qid, uid).Scan(&permissions.Gm)

  rows, err := db.Query("select c.cid, c.char_name from characters as c, quest_members as qm where qm.qid=? and qm.cid=c.cid and c.uid=?", qid, uid)
  if (err != nil) {
    log.Fatal("Error checking gm")
  }
  var characters []Character
  for rows.Next() {
    character := Character{}
    err = rows.Scan(&character.Cid, &character.Char_name)
    if err != nil {
      log.Fatal(err)
    }
    characters = append(characters, character)
  }
  permissions.Characters = characters
  DBUtils.CloseDB(db)
  return permissions
}


func CreatePost(qid int, uid int, cid int, text string) Post {
  db := DBUtils.OpenDB();
  stmt, err := db.Prepare("INSERT INTO posts (qid,cid,uid,post_text,post_status,post_date) VALUES(?,?,?,?,0,now())")
  if (err != nil) {
    log.Fatal(err)
  }
  defer stmt.Close()
  res, err := stmt.Exec(qid,cid,uid,text)
  if (err != nil) {
    log.Fatal(err)
  }
  id, err := res.LastInsertId()
  if (err != nil) {
    log.Fatal(err)
  }
  DBUtils.CloseDB(db)
  return GetPost(id)
}

func GetPost(pid int64) Post {
  db := DBUtils.OpenDB();
  post := Post{}
  post.Pid = pid
  db.QueryRow("select qid,uid,cid,UNIX_TIMESTAMP(post_date),post_text from posts where pid=?", pid).Scan(&post.Qid,&post.Uid,&post.Cid,&post.Stamp,&post.Text)
  if post.Cid == 0 {
    db.QueryRow("select login_name from users where uid = ?", post.Uid).Scan(&post.Poster)
    post.Poster += " - GM"
  } else {
    db.QueryRow("select char_name from characters where cid = ?", post.Cid).Scan(&post.Poster) 
  }
  DBUtils.CloseDB(db)
  return post
}

func GetPosts(qid int, start int, length int, order string) []Post {
  var posts []Post
  db := DBUtils.OpenDB();
  log.Println(order);
  rows, err := db.Query("select pid,qid,uid,cid,post_text,UNIX_TIMESTAMP(post_date) from posts WHERE qid = ? ORDER BY post_date " + order + " LIMIT ?, ?", qid, start, length)
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
  DBUtils.CloseDB(db)
  return posts
}
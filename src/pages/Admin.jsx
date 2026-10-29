import { useEffect, useState } from "react";
import "./Admin.css";
import { API, getMediaUrl, postJson } from "../utils";

const MEDIA_LABEL = { image: "🖼️ Ảnh", video: "🎬 Video", file: "📎 Tệp" };

function Admin() {
  let user = null;
  try {
    user = JSON.parse(localStorage.getItem("user") || "null");
  } catch {
    user = null;
  }

  // TABS: "posts" | "users"
  const [activeTab, setActiveTab] = useState("posts");

  // STATE BÀI VIẾT
  const [posts, setPosts] = useState([]);
  const [loadingPosts, setLoadingPosts] = useState(true);
  const [searchPost, setSearchPost] = useState("");
  const [deletingId, setDeletingId] = useState(null);

  // STATE NGƯỜI DÙNG / SINH VIÊN
  const [usersList, setUsersList] = useState([]);
  const [loadingUsers, setLoadingUsers] = useState(false);
  const [searchUser, setSearchUser] = useState("");
  const [togglingBanId, setTogglingBanId] = useState(null);

  // LẤY TẤT CẢ BÀI VIẾT
  useEffect(() => {
    if (!user?.id) {
      setLoadingPosts(false);
      return;
    }

    const fetchPosts = async () => {
      try {
        const response = await fetch(`${API}/posts/posts.php?user_id=${user.id}`);
        const data = await response.json();
        setPosts(Array.isArray(data) ? data : []);
      } catch (error) {
        console.log("Lỗi lấy bài viết:", error);
      } finally {
        setLoadingPosts(false);
      }
    };

    fetchPosts();
  }, [user?.id]);

  // LẤY DANH SÁCH SINH VIÊN / NGƯỜI DÙNG
  const fetchUsers = async () => {
    if (!user?.id) return;
    setLoadingUsers(true);
    try {
      const response = await fetch(`${API}/admin/users.php?admin_id=${user.id}`);
      const data = await response.json();
      setUsersList(Array.isArray(data) ? data : []);
    } catch (error) {
      console.log("Lỗi lấy danh sách người dùng:", error);
    } finally {
      setLoadingUsers(false);
    }
  };

  useEffect(() => {
    if (activeTab === "users" && usersList.length === 0) {
      fetchUsers();
    }
  }, [activeTab]);

  // XÓA BÀI VIẾT
  const handleDeletePost = async (post) => {
    if (!window.confirm(`Xóa bài viết của "${post.name}"? Thao tác này không thể hoàn tác.`)) {
      return;
    }

    setDeletingId(post.id);

    try {
      await postJson(`${API}/posts/manage.php`, { action: "delete", post_id: post.id, user_id: user.id });
      setPosts((current) => current.filter((item) => item.id !== post.id));
    } catch (error) {
      alert(error.message);
    } finally {
      setDeletingId(null);
    }
  };

  // KHÓA / MỞ KHÓA TÀI KHOẢN SINH VIÊN
  const handleToggleBan = async (targetUser) => {
    const isCurrentlyBanned = Number(targetUser.is_banned) === 1;
    const actionText = isCurrentlyBanned ? "MỞ KHÓA" : "KHÓA";

    if (!window.confirm(`Bạn có chắc muốn ${actionText} tài khoản của "${targetUser.name}"?`)) {
      return;
    }

    setTogglingBanId(targetUser.id);

    try {
      const res = await postJson(`${API}/admin/toggle-ban.php`, {
        admin_id: user.id,
        user_id: targetUser.id,
        is_banned: isCurrentlyBanned ? 0 : 1,
      });

      alert(res.message || "Cập nhật thành công!");

      setUsersList((currentList) =>
        currentList.map((u) => (u.id === targetUser.id ? { ...u, is_banned: isCurrentlyBanned ? 0 : 1 } : u)),
      );
    } catch (error) {
      alert(error.message || "Thao tác thất bại!");
    } finally {
      setTogglingBanId(null);
    }
  };

  // TÌM KIẾM BÀI VIẾT
  const postKeyword = searchPost.toLowerCase().trim();
  const filteredPosts = posts.filter(
    (post) => post.content?.toLowerCase().includes(postKeyword) || post.name?.toLowerCase().includes(postKeyword),
  );

  // TÌM KIẾM SINH VIÊN
  const userKeyword = searchUser.toLowerCase().trim();
  const filteredUsers = usersList.filter(
    (u) =>
      u.name?.toLowerCase().includes(userKeyword) ||
      u.email?.toLowerCase().includes(userKeyword) ||
      u.student_code?.toLowerCase().includes(userKeyword) ||
      u.class?.toLowerCase().includes(userKeyword),
  );

  return (
    <div className="admin-page">
      <header className="admin-header">
        <div>
          <h1>🛡️ Quản trị IT CONNECT</h1>
          <span>Xin chào, {user?.name || "Quản trị viên"}</span>
        </div>

        <button
          className="admin-logout"
          onClick={() => {
            localStorage.removeItem("user");
            window.location.href = "/";
          }}
        >
          🚪 Đăng xuất
        </button>
      </header>

      <main className="admin-main">
        {/* THANH CHUYỂN TAB */}
        <div className="admin-tabs">
          <button
            className={`admin-tab-btn ${activeTab === "posts" ? "active" : ""}`}
            onClick={() => setActiveTab("posts")}
          >
            📝 Quản lý bài viết ({posts.length})
          </button>
          <button
            className={`admin-tab-btn ${activeTab === "users" ? "active" : ""}`}
            onClick={() => setActiveTab("users")}
          >
            👥 Quản lý sinh viên ({usersList.length})
          </button>
        </div>

        {/* ================= TAB 1: BÀI VIẾT ================= */}
        {activeTab === "posts" && (
          <div>
            <div className="admin-toolbar">
              <h2>Danh sách bài viết ({posts.length})</h2>

              <input
                type="text"
                placeholder="Tìm theo nội dung hoặc tên người đăng..."
                value={searchPost}
                onChange={(e) => setSearchPost(e.target.value)}
              />
            </div>

            {loadingPosts ? (
              <p className="admin-empty">Đang tải bài viết...</p>
            ) : filteredPosts.length === 0 ? (
              <p className="admin-empty">Không tìm thấy bài viết nào.</p>
            ) : (
              filteredPosts.map((post) => (
                <article className="admin-post" key={post.id}>
                  <div className="admin-post-info">
                    <div className="admin-post-top">
                      <strong>{post.name}</strong>
                      <span>{post.created_at ? new Date(post.created_at).toLocaleString("vi-VN") : ""}</span>
                      {MEDIA_LABEL[post.media_type] && <em>{MEDIA_LABEL[post.media_type]}</em>}
                    </div>

                    <p>{post.content || "(Bài viết không có nội dung chữ)"}</p>

                    {post.media_type === "image" && post.image && (
                      <img src={getMediaUrl(post.image)} alt="Ảnh bài viết" className="admin-post-image" />
                    )}

                    {post.media_type === "video" && post.file && (
                      <video src={getMediaUrl(post.file)} controls className="admin-post-video" />
                    )}

                    {post.media_type === "file" && post.file && (
                      <div className="admin-post-file">
                        <span>📎 {post.file.split("/").pop()}</span>
                        <a
                          href={getMediaUrl(post.file)}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="admin-file-download"
                        >
                          Mở / Tải tệp
                        </a>
                      </div>
                    )}

                    <small>
                      ❤️ {post.like_count} lượt thích · 💬 {post.comment_count} bình luận
                    </small>
                  </div>

                  <button
                    className="admin-delete"
                    disabled={deletingId === post.id}
                    onClick={() => handleDeletePost(post)}
                  >
                    {deletingId === post.id ? "Đang xóa..." : "🗑️ Xóa"}
                  </button>
                </article>
              ))
            )}
          </div>
        )}

        {/* ================= TAB 2: NGƯỜI DÙNG / SINH VIÊN ================= */}
        {activeTab === "users" && (
          <div>
            <div className="admin-toolbar">
              <h2>Danh sách sinh viên ({usersList.length})</h2>

              <input
                type="text"
                placeholder="Tìm theo tên, email, MSSV, lớp..."
                value={searchUser}
                onChange={(e) => setSearchUser(e.target.value)}
              />
            </div>

            {loadingUsers ? (
              <p className="admin-empty">Đang tải danh sách sinh viên...</p>
            ) : filteredUsers.length === 0 ? (
              <p className="admin-empty">Không tìm thấy sinh viên nào.</p>
            ) : (
              filteredUsers.map((targetUser) => {
                const isBanned = Number(targetUser.is_banned) === 1;
                const isAdmin = targetUser.role?.toLowerCase() === "admin";

                return (
                  <div key={targetUser.id} className={`admin-user-card ${isBanned ? "is-banned" : ""}`}>
                    <div className="admin-user-left">
                      <div className="admin-user-avatar">
                        {targetUser.avatar ? (
                          <img src={getMediaUrl(targetUser.avatar)} alt="Avatar" />
                        ) : targetUser.name ? (
                          targetUser.name.charAt(0).toUpperCase()
                        ) : (
                          "U"
                        )}
                      </div>

                      <div className="admin-user-meta">
                        <div className="admin-user-name-row">
                          <span className="admin-user-name">{targetUser.name}</span>
                          {isAdmin ? (
                            <span className="admin-badge-admin">Quản trị viên</span>
                          ) : isBanned ? (
                            <span className="admin-badge-banned">🚫 Đã bị khóa</span>
                          ) : (
                            <span className="admin-badge-active">🟢 Hoạt động</span>
                          )}
                        </div>

                        <div className="admin-user-sub">
                          <span>📧 {targetUser.email}</span>
                          {targetUser.student_code && <span>🆔 MSSV: {targetUser.student_code}</span>}
                          {targetUser.class && <span>🎓 Lớp: {targetUser.class}</span>}
                        </div>

                        <div className="admin-user-stats">
                          📝 {targetUser.post_count || 0} bài viết · 💬 {targetUser.comment_count || 0} bình luận · Tham
                          gia: {targetUser.created_at ? new Date(targetUser.created_at).toLocaleDateString("vi-VN") : "N/A"}
                        </div>
                      </div>
                    </div>

                    {!isAdmin && (
                      <div>
                        {isBanned ? (
                          <button
                            className="admin-unban-btn"
                            disabled={togglingBanId === targetUser.id}
                            onClick={() => handleToggleBan(targetUser)}
                          >
                            {togglingBanId === targetUser.id ? "Đang xử lý..." : "🟢 Mở khóa"}
                          </button>
                        ) : (
                          <button
                            className="admin-ban-btn"
                            disabled={togglingBanId === targetUser.id}
                            onClick={() => handleToggleBan(targetUser)}
                          >
                            {togglingBanId === targetUser.id ? "Đang xử lý..." : "🚫 Khóa tài khoản"}
                          </button>
                        )}
                      </div>
                    )}
                  </div>
                );
              })
            )}
          </div>
        )}
      </main>
    </div>
  );
}

export default Admin;

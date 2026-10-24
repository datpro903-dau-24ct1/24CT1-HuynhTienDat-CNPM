with open('src/pages/Account.jsx', 'r', encoding='utf-8') as f:
    c = f.read()

# 1. Add states
state_target = """  const [editForm, setEditForm] = useState({
    name: "",
    email: "",
    student_code: "",
    class: "",
    date_of_birth: "",
  });"""

state_replacement = """  const [editForm, setEditForm] = useState({
    name: "",
    email: "",
    student_code: "",
    class: "",
    date_of_birth: "",
  });

  // CHANGE PASSWORD
  const [showPasswordModal, setShowPasswordModal] = useState(false);
  const [passwordForm, setPasswordForm] = useState({
    current_password: "",
    new_password: "",
    confirm_password: "",
  });
  const [changingPassword, setChangingPassword] = useState(false);
  const [passwordMessage, setPasswordMessage] = useState("");
  const [showPasswordText, setShowPasswordText] = useState(false);"""

c = c.replace(state_target, state_replacement)

# 2. Add handlers before handleLogout
handler_target = """  // ĐĂNG XUẤT

  const handleLogout = () => {"""

handler_replacement = """  // CHANGE PASSWORD HANDLERS

  const handleOpenPasswordModal = () => {
    setPasswordForm({
      current_password: "",
      new_password: "",
      confirm_password: "",
    });
    setPasswordMessage("");
    setShowPasswordText(false);
    setShowPasswordModal(true);
  };

  const handleClosePasswordModal = () => {
    if (changingPassword) return;
    setShowPasswordModal(false);
    setPasswordMessage("");
  };

  const handlePasswordChange = (e) => {
    const { name, value } = e.target;
    setPasswordForm((prev) => ({
      ...prev,
      [name]: value,
    }));
  };

  const handleSavePassword = async (e) => {
    e.preventDefault();
    if (changingPassword) return;

    if (!passwordForm.current_password || !passwordForm.new_password || !passwordForm.confirm_password) {
      setPasswordMessage("Vui lòng nhập đầy đủ các trường!");
      return;
    }

    if (passwordForm.new_password.length < 6) {
      setPasswordMessage("Mật khẩu mới phải có ít nhất 6 ký tự!");
      return;
    }

    if (passwordForm.new_password !== passwordForm.confirm_password) {
      setPasswordMessage("Mật khẩu xác nhận không khớp!");
      return;
    }

    if (passwordForm.current_password === passwordForm.new_password) {
      setPasswordMessage("Mật khẩu mới không được trùng với mật khẩu hiện tại!");
      return;
    }

    try {
      setChangingPassword(true);
      setPasswordMessage("");

      const response = await fetch(`${API}/account/change-password.php`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          user_id: user.id,
          current_password: passwordForm.current_password,
          new_password: passwordForm.new_password,
          confirm_password: passwordForm.confirm_password,
        }),
      });

      const data = await response.json();

      if (!response.ok) {
        setPasswordMessage(data.message || "Đổi mật khẩu thất bại!");
        return;
      }

      setPasswordMessage("Đổi mật khẩu thành công!");
      setTimeout(() => {
        setShowPasswordModal(false);
        setPasswordMessage("");
      }, 1500);
    } catch (error) {
      console.log("Lỗi đổi mật khẩu:", error);
      setPasswordMessage("Không thể kết nối đến máy chủ!");
    } finally {
      setChangingPassword(false);
    }
  };

  // ĐĂNG XUẤT

  const handleLogout = () => {"""

c = c.replace(handler_target, handler_replacement)

# 3. Add button in profile-actions
button_target = """            <div className="profile-actions">
              <button type="button" onClick={handleOpenEdit}>
                ✏️ Chỉnh sửa thông tin
              </button>

              <button type="button" className="logout-btn" onClick={handleLogout}>
                🚪 Đăng xuất
              </button>
            </div>"""

button_replacement = """            <div className="profile-actions">
              <button type="button" onClick={handleOpenEdit}>
                ✏️ Chỉnh sửa thông tin
              </button>

              <button type="button" className="change-password-btn" onClick={handleOpenPasswordModal}>
                🔑 Đổi mật khẩu
              </button>

              <button type="button" className="logout-btn" onClick={handleLogout}>
                🚪 Đăng xuất
              </button>
            </div>"""

c = c.replace(button_target, button_replacement)

# 4. Add modal JSX at the end
modal_target = """      {showEdit && (
        <div
          className="edit-overlay"
          onMouseDown={(e) => {
            if (e.target === e.currentTarget && !saving) {
              handleCloseEdit();
            }
          }}
        >
          <div
            className="edit-modal"
            onMouseDown={(e) => {
              e.stopPropagation();
            }}
          >
            {/* HEADER */}

            <div className="edit-modal-header">
              <div>
                <h2>✏️ Chỉnh sửa thông tin</h2>

                <p>Cập nhật thông tin cá nhân của bạn</p>
              </div>

              <button type="button" className="edit-close" onClick={handleCloseEdit} disabled={saving}>
                ✕
              </button>
            </div>

            {/* FORM */}

            <form className="edit-form" onSubmit={handleSave}>
              {/* HỌ TÊN */}

              <div className="edit-form-group">
                <label>Họ và tên</label>

                <input
                  type="text"
                  name="name"
                  value={editForm.name}
                  onChange={handleChange}
                  placeholder="Nhập họ và tên"
                  disabled={saving}
                  required
                />
              </div>

              {/* EMAIL */}

              <div className="edit-form-group">
                <label>Email</label>

                <input
                  type="email"
                  name="email"
                  value={editForm.email}
                  onChange={handleChange}
                  placeholder="Nhập email"
                  disabled={saving}
                  required
                />
              </div>

              {/* MÃ SINH VIÊN */}

              <div className="edit-form-group">
                <label>Mã sinh viên</label>

                <input
                  type="text"
                  name="student_code"
                  value={editForm.student_code}
                  onChange={handleChange}
                  placeholder="Nhập mã sinh viên"
                  disabled={saving}
                  required
                />
              </div>

              {/* LỚP */}

              <div className="edit-form-group">
                <label>Lớp</label>

                <input
                  type="text"
                  name="class"
                  value={editForm.class}
                  onChange={handleChange}
                  placeholder="Nhập lớp"
                  disabled={saving}
                  required
                />
              </div>

              {/* NGÀY SINH */}

              <div className="edit-form-group">
                <label>Ngày tháng năm sinh</label>

                <input
                  type="date"
                  name="date_of_birth"
                  value={editForm.date_of_birth}
                  onChange={handleChange}
                  disabled={saving}
                  required
                />
              </div>

              {/* MESSAGE */}

              {message && (
                <div
                  className={message.includes("thành công") ? "edit-message success" : "edit-message error"}
                >
                  {message}
                </div>
              )}

              {/* BUTTON */}

              <div className="edit-form-actions">
                <button type="button" className="edit-cancel" onClick={handleCloseEdit} disabled={saving}>
                  Hủy
                </button>

                <button type="submit" className="edit-save" disabled={saving}>
                  {saving ? "Đang lưu..." : "💾 Lưu thay đổi"}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}"""

modal_replacement = """      {showEdit && (
        <div
          className="edit-overlay"
          onMouseDown={(e) => {
            if (e.target === e.currentTarget && !saving) {
              handleCloseEdit();
            }
          }}
        >
          <div
            className="edit-modal"
            onMouseDown={(e) => {
              e.stopPropagation();
            }}
          >
            {/* HEADER */}

            <div className="edit-modal-header">
              <div>
                <h2>✏️ Chỉnh sửa thông tin</h2>

                <p>Cập nhật thông tin cá nhân của bạn</p>
              </div>

              <button type="button" className="edit-close" onClick={handleCloseEdit} disabled={saving}>
                ✕
              </button>
            </div>

            {/* FORM */}

            <form className="edit-form" onSubmit={handleSave}>
              {/* HỌ TÊN */}

              <div className="edit-form-group">
                <label>Họ và tên</label>

                <input
                  type="text"
                  name="name"
                  value={editForm.name}
                  onChange={handleChange}
                  placeholder="Nhập họ và tên"
                  disabled={saving}
                  required
                />
              </div>

              {/* EMAIL */}

              <div className="edit-form-group">
                <label>Email</label>

                <input
                  type="email"
                  name="email"
                  value={editForm.email}
                  onChange={handleChange}
                  placeholder="Nhập email"
                  disabled={saving}
                  required
                />
              </div>

              {/* MÃ SINH VIÊN */}

              <div className="edit-form-group">
                <label>Mã sinh viên</label>

                <input
                  type="text"
                  name="student_code"
                  value={editForm.student_code}
                  onChange={handleChange}
                  placeholder="Nhập mã sinh viên"
                  disabled={saving}
                  required
                />
              </div>

              {/* LỚP */}

              <div className="edit-form-group">
                <label>Lớp</label>

                <input
                  type="text"
                  name="class"
                  value={editForm.class}
                  onChange={handleChange}
                  placeholder="Nhập lớp"
                  disabled={saving}
                  required
                />
              </div>

              {/* NGÀY SINH */}

              <div className="edit-form-group">
                <label>Ngày tháng năm sinh</label>

                <input
                  type="date"
                  name="date_of_birth"
                  value={editForm.date_of_birth}
                  onChange={handleChange}
                  disabled={saving}
                  required
                />
              </div>

              {/* MESSAGE */}

              {message && (
                <div
                  className={message.includes("thành công") ? "edit-message success" : "edit-message error"}
                >
                  {message}
                </div>
              )}

              {/* BUTTON */}

              <div className="edit-form-actions">
                <button type="button" className="edit-cancel" onClick={handleCloseEdit} disabled={saving}>
                  Hủy
                </button>

                <button type="submit" className="edit-save" disabled={saving}>
                  {saving ? "Đang lưu..." : "💾 Lưu thay đổi"}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* MODAL ĐỔI MẬT KHẨU */}
      {showPasswordModal && (
        <div
          className="edit-overlay"
          onMouseDown={(e) => {
            if (e.target === e.currentTarget && !changingPassword) {
              handleClosePasswordModal();
            }
          }}
        >
          <div
            className="edit-modal"
            onMouseDown={(e) => {
              e.stopPropagation();
            }}
          >
            {/* HEADER */}
            <div className="edit-modal-header">
              <div>
                <h2>🔑 Đổi mật khẩu</h2>
                <p>Cập nhật mật khẩu mới cho tài khoản của bạn</p>
              </div>

              <button
                type="button"
                className="edit-close"
                onClick={handleClosePasswordModal}
                disabled={changingPassword}
              >
                ✕
              </button>
            </div>

            {/* FORM */}
            <form className="edit-form" onSubmit={handleSavePassword}>
              {/* MẬT KHẨU HIỆN TẠI */}
              <div className="edit-form-group">
                <label>Mật khẩu hiện tại</label>
                <input
                  type={showPasswordText ? "text" : "password"}
                  name="current_password"
                  value={passwordForm.current_password}
                  onChange={handlePasswordChange}
                  placeholder="Nhập mật khẩu hiện tại"
                  disabled={changingPassword}
                  required
                />
              </div>

              {/* MẬT KHẨU MỚI */}
              <div className="edit-form-group">
                <label>Mật khẩu mới (tối thiểu 6 ký tự)</label>
                <input
                  type={showPasswordText ? "text" : "password"}
                  name="new_password"
                  value={passwordForm.new_password}
                  onChange={handlePasswordChange}
                  placeholder="Nhập mật khẩu mới"
                  disabled={changingPassword}
                  required
                />
              </div>

              {/* XÁC NHẬN MẬT KHẨU MỚI */}
              <div className="edit-form-group">
                <label>Xác nhận mật khẩu mới</label>
                <input
                  type={showPasswordText ? "text" : "password"}
                  name="confirm_password"
                  value={passwordForm.confirm_password}
                  onChange={handlePasswordChange}
                  placeholder="Nhập lại mật khẩu mới"
                  disabled={changingPassword}
                  required
                />
              </div>

              {/* HIỂN THỊ MẬT KHẨU CHECKBOX */}
              <div className="show-password-row">
                <label>
                  <input
                    type="checkbox"
                    checked={showPasswordText}
                    onChange={(e) => setShowPasswordText(e.target.checked)}
                  />
                  <span>Hiển thị mật khẩu</span>
                </label>
              </div>

              {/* MESSAGE */}
              {passwordMessage && (
                <div
                  className={
                    passwordMessage.includes("thành công") ? "edit-message success" : "edit-message error"
                  }
                >
                  {passwordMessage}
                </div>
              )}

              {/* BUTTONS */}
              <div className="edit-form-actions">
                <button
                  type="button"
                  className="edit-cancel"
                  onClick={handleClosePasswordModal}
                  disabled={changingPassword}
                >
                  Hủy
                </button>

                <button type="submit" className="edit-save" disabled={changingPassword}>
                  {changingPassword ? "Đang xử lý..." : "🔒 Cập nhật mật khẩu"}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}"""

c = c.replace(modal_target, modal_replacement)

with open('src/pages/Account.jsx', 'w', encoding='utf-8', newline='\n') as f:
    f.write(c)

print("Account.jsx successfully updated with change password modal!")


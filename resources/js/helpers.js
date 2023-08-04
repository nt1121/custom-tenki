export default {
    validateEmail: function (email) {
        if (email === 0) {
            return '入力してください。';
        } else if (email > 150) {
            return '150文字以下で入力してください。';
        } else {
            const emailRegex = /^[\w\-._]+@[\w\-._]+\.[A-Za-z]+$/;
            if (emailRegex.test(email)) {
                return '';
            } else {
                return 'メールアドレスの形式で入力してください。';
            }
        }
    },
    validatePassword: function (password) {
        if (password.length === 0) {
            return '入力してください。';
        } else if (password.length < 8) {
            return '8文字以上で入力してください。';
        } else if (password.length > 255) {
            return '255文字以下で入力してください。';
        } else {
            const passwordRegex = /^[a-zA-Z0-9]+$/;
            if (passwordRegex.test(password)) {
                return '';
            } else {
                return '半角英数で入力してください。';
            }
        }
    }
}
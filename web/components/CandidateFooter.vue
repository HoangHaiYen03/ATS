<template>
    <div>
        <div class="footer-container bg-primary text-white py-8">
            <div class="container mx-auto px-4 flex justify-between">
                <!-- Logo and Mission Section -->
                <div class="mb-8">
                    <img
                        src="@/assets/images/logo-2.png"
                        alt="Five-Star English"
                        class="logo mb-4"
                    >
                    <p class="max-w-xl">
                        Sứ mệnh của chúng tôi là mang đến việc làm chất lượng cao, đa dạng, đa ngành nghề, đa lĩnh vực, trên khắp Việt Nam.
                    </p>
                    <p class="mt-4">33 Lạc Trung, Hai Bà Trưng, Hà Nội</p>
                    <p>Email: recruitment@atlantic.vn</p>
                </div>

                <!-- Menu and Subscribe Section -->
                <div class="grid md:grid-cols-2 gap-8">
                    <!-- Menu Links -->
                    <div>
                        <h3 class="text-xl font-bold mb-4">MENU</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <nuxt-link to="/ats" class="block mb-2 hover:text-gray-300">Trang chủ</nuxt-link>
                                <nuxt-link to="/ats/about" class="block mb-2 hover:text-gray-300">Về chúng tôi</nuxt-link>
                                <nuxt-link to="/ats/jobs" class="block mb-2 hover:text-gray-300">Việc làm</nuxt-link>
                            </div>
                            <!-- <div>
                                <nuxt-link to="/ats/contact" class="block mb-2 hover:text-gray-300">Liên hệ</nuxt-link>
                            </div> -->
                        </div>
                    </div>

                    <!-- Subscribe Section -->
                    <div>
                        <h3 class="text-xl font-bold mb-4">ĐĂNG KÝ NHẬN THÔNG TIN CẬP NHẬT:</h3>
                        <div class="flex mb-4">
                            <input
                                type="email"
                                placeholder="Địa chỉ email của bạn"
                                class="p-2 flex-grow text-gray-700 rounded-l"
                            >
                            <button class="bg-orange-500 text-white px-6 py-2 rounded-r hover:bg-orange-600">
                                ĐĂNG KÝ
                            </button>
                        </div>
                        <!-- Social Media Icons -->
                        <div class="flex space-x-4">
                            <a href="#" class="hover:opacity-80">
                                <span class="material-icons text-3xl">facebook</span>
                            </a>
                            <a href="#" class="hover:opacity-80">
                                <span class="material-icons text-3xl">linkedin</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Copyright -->
        </div>
        <div style="background-color: rgb(79 70 168)" class="text-white mt-8 text-center border-t border-gray-600 p-4">
            <p>© Bản quyền 2025 AG. Đã đăng ký bản quyền</p>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'CandidateFooter',
    };
</script>

<style scoped>
.footer-container {
    background-color: #28497c;
}

.logo {
    height: 50px;
}

input::placeholder {
    color: #999;
}

.bg-primary {
    background-color: rgb(115 103 240);
}
</style>

<template>
  <div>
    <div class="footer">
      <el-row :gutter="5">
        <el-col :span="6">
          <div class="face">
            <i class="fa-brands fa-facebook"></i>
            <i class="fa-brands fa-twitter"></i>
            <i class="fa-brands fa-youtube"></i>
            <i class="fa-brands fa-instagram"></i>
          </div>
        </el-col>
        <el-col :span="9">
          <div class="about">
            <h6>{{ $t('ĐĂNG KÍ NHẬN TIN')}}</h6>
            <h7>{{ $t('Các chương trình khuyến mãi sẽ tự động gửi đến bạn')}}</h7>
          </div>
        </el-col>
        <el-col :span="9">
          <div class="newsletter">
            <el-input
              v-model="email"
              v-bind:placeholder="$t('Nhập email của bạn')"
              clearable
              class="newsletter-input"
            />
            <el-button type="primary" @click="submitEmail">{{ $t('Gửi')}}</el-button>
          </div>
        </el-col>
      </el-row>
    </div>
    <div class="footer1">
      <el-row :gutter="10">
        <el-col :span="6">
          <div class="logo">
            <img src="@/assets/img/logo1.png" alt="" style="width: 100%" />
            <span style="margin-left: 5px;font-weight: 700đ;">CÔNG TY TNHH SAOMAIFAMILY</span><br/>
            <div style="margin-left: 10px;">
                <span>
                <em class="fa fa-file-text"></em>
                M.S.D.N: 0101010101
            </span><br/>
            <span>
                <em class="fa fa-map-marker"></em>
                Địa chỉ: 61 Bình Quý, Phường 10, TP Vũng Tàu
            </span><br/>
            <span>
                <em class="fa fa-phone"></em>
                Điện thoại: 1900599997
            </span><br/>
            <span>
                <em class="fa fa-envelope"></em>
                Email: booking.saomaifly@gmail.com
            </span><br/>
            <span>
                <em class="fa fa-globe"></em>
                Website: http://vetauduongsat.com
            </span><br/>
            </div>
            <div class="visa">
              <img src="/src/assets/img/banking.png" alt="">
              <img src="/src/assets/img/master.png" alt="">
              <img src="/src/assets/img/visa.png" alt="">
              <img src="/src/assets/img/money.png" alt="">
            </div>
          </div>
          
        </el-col>
        <el-col :span="4" class="footer1-col">
            <div class="duong1">
                <h6>Đường Sắt Hà Nội</h6>
                <span>Hà Nội -> Hải Phòng</span> <br/>
                <span>Hà Nội -> Lào Cai</span><br/>
                <span>Hà Nội -> Vinh</span><br/>
                <span>Hà Nội -> Phố Lu</span><br/>
                <span>Hà Nội -> Đã Nẵng</span><br/>
                <span>Hà Nội -> Thái Nguyên</span><br/>
                <span>Hà Nội -> Hạ Long</span><br/>
                <span>Hà Nội -> Sài Gòn</span><br/>
                <h6>Đường Sắt Sài Gòn</h6>
                <span>Sài Gòn -> Nha Trang</span><br/>
                <span>Sài Gòn -> Huế</span><br/>
                <span>Sài Gòn -> Quy Nhơn</span><br/>
                <span>Sài Gòn -> Phan Thiết</span><br/>
                <span>Sài Gòn -> Hà Nội</span><br/>
            </div>
        </el-col>
        <el-col :span="5" class="footer1-col">
            <div class="duong2">
                <h6>Đường Sắt Vinh</h6>
                <span>Vinh -> Thanh Hoá</span> <br/>
                <span>Vinh -> Huế</span><br/>
                <span>Vinh -> Đà Nãng</span><br/>
                <span>Vinh -> Diêu Trì</span><br/>
                <span>Vinh -> Phan Thiết</span><br/>
                <span>Vinh -> Sài Gòn</span><br/>
                <h6>Đường Sắt Nha Trang</h6>
                <span>Nha Trang -> Phan Thiết</span><br/>
                <span>Nha Trang -> Sài Gòn</span><br/>
            </div>
        </el-col>
        <el-col :span="5" class="footer1-col">  
            <div class="duong3">
                <h6>Đường Sắt Đà Nẵng</h6>
                <span>Đà Nẵng -> Huế</span> <br/>
                <span>Đà Nẵng -> Quy Nhơn</span><br/>
                <span>Đà Nẵng -> Nha Trang</span><br/>
                <span>Đà Nẵng -> Sài Gòn</span><br/>
                <h6>Đường Sắt Quy Nhơn</h6>
                <span>Quy Nhơn -> Nha Trang</span><br/>
                <span>Quy Nhơn -> Sài Gòn</span><br/>
            </div>
        </el-col>
        <el-col :span="4" class="footer1-col">
            <div class="duong4">
                <h6>Thông tin cần thiết</h6>
                <span> Chính sách bảo mật</span><br/>
                <span>Điều khoản sử dụng</span><br/>
                <span>Hướng dẫn mua vé</span><br/>
                <span>Điều khoản thanh toán </span><br/>
                <span>Quy định đổi trả vé</span><br/>
                <span>Quy định hàng hoá đi kèm</span><br/>

            </div>
        </el-col>
      </el-row>
    </div>
    <div class="footer0">
        <span style="font-size: 13px;color: white">
            © {{ $t('Bản quyền thuộc về Vé tàu hoả Bắc Nam tại Saomaifly. Thiết kế bởi ĐỖ MẠNH CƯỜNG.')}}
        </span>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";

const email = ref("");
const submitEmail = () => {
  if (email.value) {
    alert("Đã gửi yêu cầu hỗ trợ");
    email.value = "";
  } else {
    alert("Vui lòng nhập email hợp lệ");
  }
};
</script>

<style scoped>
@import url(@/assets/css/footer.css);
</style>

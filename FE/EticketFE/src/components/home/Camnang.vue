<template>
  <div>
    <h2 class="text-xl font-bold">{{ $t('Thông tin hữu ích khi đi tàu')}}</h2>
    <p class="text-gray-600 mb-4">{{ $t('Câu hỏi thường gặp khi đặt vé tàu')}}</p>

    <el-row :gutter="20" class="mb-6 mb-3">
      <el-col :span="8">
        <el-button
          :type="selectedTab === 'tab1' ? 'primary' : 'default'"
          class="custom-button w-full"
          @click="changeTab('tab1')"
        >
          {{ $t('Làm thế nào để đặt vé tàu hỏa trên iVIVU?')}}
        </el-button>
      </el-col>
      <el-col :span="8">
        <el-button
          :type="selectedTab === 'tab2' ? 'primary' : 'default'"
          class="custom-button w-full"
          @click="changeTab('tab2')"
        >
          {{ $t('Tôi có thể thanh toán bằng những phương thức nào?')}}
        </el-button>
      </el-col>
      <el-col :span="8">
        <el-button
          :type="selectedTab === 'tab3' ? 'primary' : 'default'"
          class="custom-button w-full"
          @click="changeTab('tab3')"
        >
         {{ $t('Thông tin cần cung cấp khi đặt vé')}}
        </el-button>
      </el-col>
    </el-row>

    <el-row :gutter="20">
      <el-col :span="8">
        <el-button
          :type="selectedTab === 'tab4' ? 'primary' : 'default'"
          class="custom-button w-full"
          @click="changeTab('tab4')"
        >
          {{ $t('Đi tàu cần mang theo giấy tờ gì?')}}
        </el-button>
      </el-col>
      <el-col :span="8">
        <el-button
          :type="selectedTab === 'tab5' ? 'primary' : 'default'"
          class="custom-button w-full"
          @click="changeTab('tab5')"
        >
          {{ $t('Cách kiểm tra vé, thay đổi hoặc hủy vé đã đặt')}}
        </el-button>
      </el-col>
      <el-col :span="8">
        <el-button
          :type="selectedTab === 'tab6' ? 'primary' : 'default'"
          class="custom-button w-full"
          @click="changeTab('tab6')"
        >
          {{ $t('Các loại tàu trên đường sắt Việt Nam')}}
        </el-button>
      </el-col>
    </el-row>

    <div class="mt-3 flex items-start rounded bg-gray-50 p-4 gap-4">
      <div class="w-1/2">
  <p v-if="selectedTab === 'tab1'">
    {{ $t('Quý khách có thể đặt vé tàu hỏa qua iVIVU theo các bước sau:') }} <br />
    <strong><span class="cham">•</span>{{ $t('Bước 1') }} : </strong>{{ $t('Truy cập trang web hoặc ứng dụng iVIVU.') }}
    <br />
    <strong><span class="cham">•</span>{{ $t('Bước 2') }} : </strong>{{ $t('Tìm kiếm và chọn chuyến tàu, chọn khoang và ghế.') }} 
    <br />
    <strong><span class="cham">•</span>{{ $t('Bước 3') }} : </strong>{{ $t('Điền thông tin hành khách và thanh toán.') }}
    <br />
    <strong><span class="cham">•</span>{{ $t('Bước 4') }} : </strong>{{ $t('Nhận mã vé điện tử. Sau khi thanh toán thành công, bạn sẽ nhận được vé qua email hoặc Zalo. Sử dụng mã vé này để lên tàu.') }}
    <br />
    {{ $t('Xem hướng dẫn chi tiết:')}} <br />
    <a href="https://www.ivivu.com/blog/2024/10/kinh-nghiem-dat-ve-tau-hoa-tu-a-den-z/" target="_blank">
      {{ $t('https://www.ivivu.com/blog/2024/10/kinh-nghiem-dat-ve-tau-hoa-tu-a-den-z/website iVIVU') }}
    </a>
  </p>

  <p v-if="selectedTab === 'tab2'">
    <strong>{{ $t('Bạn có thể thanh toán bằng các phương thức sau:') }}</strong><br />
    - {{ $t('Thẻ tín dụng (Visa, Mastercard)') }} <br />
    - {{ $t('Chuyển khoản ngân hàng') }} <br />
    - {{ $t('Thanh toán qua ví điện tử (Momo, ZaloPay)') }} <br />
    <strong>{{ $t('Lưu ý:') }}</strong><br />
    - {{ $t('Thời gian để nhập thông tin là 10 phút.') }} <br />
    - {{ $t('Thời gian để thanh toán là 20 phút.') }} <br />
    - {{ $t('Vé tàu hoả không hỗ trợ trả sau.') }}
  </p>

  <p v-if="selectedTab === 'tab3'">
    {{ $t('Khi đặt vé, bạn cần cung cấp các thông tin sau') }}: <br />
    • <strong>{{ $t('Thông tin liên hệ') }} : </strong>{{ $t('Số điện thoại liên lạc, email để nhận vé điện tử lên tàu.') }} <br />
    • <strong>{{ $t('Thông tin hành khách') }} : </strong>{{ $t('Họ và tên đầy đủ, ngày tháng năm sinh, số chứng minh nhân dân (hoặc giấy tờ tùy thân khác). Đối với hành khách 11-14 tuổi: Nhập Passport hoặc Mã định danh (nếu có), hoặc để trống.') }} <br />
    {{ $t('Lưu ý:') }} <br />
    • {{ $t('Thông tin cung cấp phải chính xác để tránh gặp sự cố khi lên tàu.') }} <br />
    • {{ $t('Đảm bảo các giấy tờ tùy thân hợp lệ để xác nhận danh tính khi cần.') }}
  </p>

  <p v-if="selectedTab === 'tab4'">
    {{ $t('Khi đi tàu, bạn cần mang theo những giấy tờ sau:') }} <br />
    - <strong>{{ $t('Giấy tờ tùy thân hợp lệ') }}: </strong>{{ $t('CMND/CCCD, hộ chiếu, hoặc giấy tờ khác có ảnh để xác minh danh tính.') }} <br />
    - <strong>{{ $t('Mã vé điện tử hoặc vé giấy') }}: </strong>{{ $t('Xuất trình thẻ lên tàu nhận qua email, zalo, hoặc vé giấy nếu đã in sẵn.') }} <br />
    {{ $t('Lưu ý:') }} <br />
    - {{ $t('Đảm bảo giấy tờ tùy thân hợp lệ để xác minh danh tính khi cần.') }} <br />
    - {{ $t('Trẻ em đi cùng cần mang theo giấy khai sinh hoặc hộ chiếu (bản gốc hoặc bản sao có công chứng).') }} <br />
    - {{ $t('Đối với học sinh, sinh viên, cần xuất trình CCCD hoặc giấy tờ tùy thân có ảnh và thẻ học sinh, sinh viên còn hiệu lực từ các trường dạy nghề, trung cấp, cao đẳng, đại học và các học viện.') }}
  </p>

  <p v-if="selectedTab === 'tab5'">
    <strong>{{ $t('Cách kiểm tra vé, thay đổi hoặc hủy vé đã đặt') }}</strong><br />
    <strong>{{ $t('1. Kiểm tra vé:') }}</strong><br />
    • {{ $t('Qua email hoặc Zalo: Kiểm tra mã vé và chi tiết chuyến đi trong email hoặc tin nhắn đã nhận sau khi đặt vé.') }} <br />
    • {{ $t('Truy cập mục Kiểm tra vé.') }} <br />
    • {{ $t('Nhập mã vé, ga đi, ga đến, ngày đi và số giấy tờ tùy thân (nhập ngày tháng năm sinh) để tra cứu chi tiết vé.') }} <br />
    <strong>{{ $t('2. Hủy vé:') }}</strong><br />
    • {{ $t('Hành khách có thể hoàn trả vé trực tuyến thông qua trang website của Đường sắt hoặc có thể đến trực tiếp nhà ga.') }} <br />
    • {{ $t('Vé chỉ có thể được trả (hoàn vé) tối thiểu trước giờ khởi hành 04 tiếng, phí trả vé là 10-20% giá vé.') }} <br />
    <strong>{{ $t('Lưu ý:') }}</strong><br />
    • {{ $t('Kiểm tra điều kiện vé trước khi hủy hoặc đổi để tránh mất phí không cần thiết.') }} <br />
    • {{ $t('Yêu cầu hủy/đổi vé cần thực hiện trong thời hạn quy định để được hỗ trợ tối ưu.') }}
  </p>

  <p v-if="selectedTab === 'tab6'">
    {{ $t('Các loại tàu trên đường sắt Việt Nam:') }} <br />
    - {{ $t('Tàu nhanh SE') }} <br />
    - {{ $t('Tàu thường') }} <br />
    - {{ $t('Tàu chất lượng cao') }} <br />
    - {{ $t('Tàu tốc hành') }}
  </p>
</div>


      <div class="w-1/2 flex justify-center">
        <img
          v-if="selectedTab === 'tab1'"
          src="@/assets/img/cn1.webp"
          alt="Đặt vé tàu"
          class="rounded max-w-full"
        />
        <img
          v-if="selectedTab === 'tab2'"
          src="@/assets/img/cn2.webp"
          alt="Phương thức thanh toán"
          class="rounded max-w-full"
        />
        <img
          v-if="selectedTab === 'tab3'"
          src="@/assets/img/cn3.webp"
          alt="Thông tin hành khách"
          class="rounded max-w-full"
        />
        <img
          v-if="selectedTab === 'tab4'"
          src="@/assets/img/cn4.webp"
          alt="Giấy tờ mang theo"
          class="rounded max-w-full"
        />
        <img
          v-if="selectedTab === 'tab5'"
          src="@/assets/img/cn5.webp"
          alt="Quản lý vé"
          class="rounded max-w-full"
        />
        <img
          v-if="selectedTab === 'tab6'"
          src="@/assets/img/cn6.webp"
          alt="Các loại tàu"
          class="rounded max-w-full"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";

const selectedTab = ref("tab1");

const changeTab = (tab) => {
  selectedTab.value = tab;
};
</script>

<style scoped>
@import url(@/assets/css/camnang.css);
</style>

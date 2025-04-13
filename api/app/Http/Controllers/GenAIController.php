<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class GenAIController extends Controller
{
    /**
     * Handle file upload and send to Google Generative AI.
     */
    public function upload(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        //     'file' => 'required|file|mimes:pdf|max:2048',
        // ]);

        // if ($validator->fails()) {
        //     return response()->json(['error' => $validator->errors()], 422);
        // }

        $file = $request->file('file');
        $path = $file->store('uploads', 'public');

        // Send file to Google Generative AI for analysis
        $analysisResult = GenAIController::sendToGenerativeAI($file);
        
        return response()->json([
            'message' => 'File uploaded and analyzed successfully',
            'file_name' => $file->getClientOriginalName(),
            'file_path' => Storage::url($path),
            'analysis_result' => $analysisResult,
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);
    }

    /**
     * Send file to Google Generative AI API for analysis.
     */
    static function sendToGenerativeAI($file): mixed
    {
        // $apiKey = env('GOOGLE_GEMINI_API_KEY');
        $apiKey = 'AIzaSyAs0F3j_BZrxSp9eHpWlRmDrhLk7wSFk2o';
        // $client = new GuzzleClient();
        $client = new \GuzzleHttp\Client([
            'headers' => [
                'Content-Encoding' => 'gzip'
            ]
        ]);
        $response = $client->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-pro-exp-03-25:generateContent?key=' . $apiKey, [
         'json' => [
                'contents' => [
                    ['parts' => [
                        [
                            'inlineData' => [
'mimeType' => 'image/png',
 'data' => base64_encode(file_get_contents($file->getRealPath()))
                        ],
                    ],
                        [
                            'text' =>  '
                        Dựa vào nội dung trên, hãy phân tích kỹ lưỡng văn bản CV tiếng Việt sau và trích xuất thông tin theo cấu trúc JSON dưới đây. Hãy đặc biệt chú ý đến các tiêu đề mục, từ khóa và định dạng thường gặp trong CV tiếng Việt.

1️⃣ Thông tin cá nhân ("personal_info")
Tìm và trích xuất các chi tiết sau, thường nằm ở đầu CV hoặc dưới các tiêu đề như "Thông tin cá nhân", "Thông tin liên hệ":

"full_name": Họ và tên đầy đủ (Tìm "Họ và tên:", hoặc tên được in đậm/lớn ở đầu). Nếu thiếu, trả về "Unknown".

"gender": Giới tính".

"phone_number": Số điện thoại (Tìm "Điện thoại:", "SĐT:", "Số điện thoại:", "Phone:"). Chuẩn hóa định dạng nếu có thể. Nếu thiếu, trả về "Unknown".

"email": Địa chỉ email (Tìm "Email:"). Nếu thiếu, trả về "Unknown".

"address": Địa chỉ (Tìm "Địa chỉ:", "Nơi ở hiện tại:"). Cố gắng bao gồm Quận/Huyện, Tỉnh/Thành phố. Nếu thiếu, trả về "Unknown".

2️⃣ Kinh nghiệm làm việc ("work_experience")
Tìm mục "Kinh nghiệm làm việc", "Quá trình công tác", hoặc tương tự. Trích xuất tất cả kinh nghiệm vào một mảng. Nếu không có, trả về: [{ "company_name": "Unknown Company", "position": "Unknown Position", "summary": "No details provided", "start_date": "Unknown", "end_date": "Present" }].

Mỗi mục công việc phải chứa:

"company_name": Tên công ty (có thể là chữ in đậm). Nếu thiếu, trả về "Unknown Company".

"position": Chức danh (Tìm "Vị trí:", "Chức vụ:", hoặc chức danh ngay dưới tên công ty). Nếu thiếu, trả về "Unknown Position".

"summary": Mô tả công việc, trách nhiệm (Thường là các gạch đầu dòng hoặc đoạn văn mô tả).  Nếu không có mô tả, trả về "No details provided".

"start_date": Ngày bắt đầu. Phân tích cú pháp các định dạng ngày tháng tiếng Việt (ví dụ: "Tháng 8, 2014", "08/2014", "2014") và chuyển đổi sang định dạng "MMM, YYYY" (ví dụ: "Aug, 2014"). Nếu không có, trả về "Unknown".

"end_date": Ngày kết thúc. Phân tích tương tự start_date. Nếu công việc ghi là "nay", "hiện tại", hoặc là công việc gần nhất không có ngày kết thúc, trả về "Present". Nếu không có, trả về "Unknown".

3️⃣ Học vấn ("education")
Tìm mục "Học vấn", "Trình độ học vấn", "Giáo dục". Trích xuất tất cả vào một mảng. Nếu không có, trả về: [{ "school_name": "Unknown School", "field_of_study": "N/A", "degree": "N/A", "start_date": "Unknown", "end_date": "Unknown" }].

Mỗi mục học vấn phải chứa:

"school_name": Tên trường/cơ sở đào tạo (Tìm "Trường:", "Tên trường:"). Nếu thiếu, trả về "Unknown School".

"field_of_study": Chuyên ngành (Tìm "Chuyên ngành:", "Ngành học:").  Nếu thiếu, trả về "N/A".

"degree": Bằng cấp.

"start_date": Năm bắt đầu (Tìm khoảng thời gian như "2015 - 2019", "Khóa 2015"). Chuyển đổi sang định dạng "YYYY". Nếu không có, trả về "Unknown".

"end_date": Năm kết thúc. Chuyển đổi sang định dạng "YYYY". Nếu không có, trả về "Unknown".

4️⃣ Kỹ năng ("skills")
Tìm mục "Kỹ năng", "Các kỹ năng khác". Trích xuất danh sách kỹ năng (tin học văn phòng, ngoại ngữ, kỹ năng mềm, kỹ năng chuyên môn). TTạo thành một danh sách các chuỗi. Nếu không tìm thấy, trả về [].

5️⃣ Kinh nghiệm tình nguyện ("volunteer_experience")
Tìm mục "Hoạt động ngoại khóa", "Tình nguyện", "Dự án đã tham gia". Trích xuất tất cả vào một mảng. Nếu không tìm thấy, trả về [].

Mỗi mục phải chứa:

"organization": Tên tổ chức/dự án. Nếu thiếu, trả về "Unknown Organization".

"role": Vai trò/Vị trí. Nếu thiếu, trả về "Unknown Role".

"summary": Mô tả hoạt động.  Nếu thiếu, trả về "No details provided".

"start_date": Ngày bắt đầu (Định dạng "MMM, YYYY"). Nếu không có, trả về "Unknown".

"end_date": Ngày kết thúc (Định dạng "MMM, YYYY"). Nếu đang tiếp diễn, trả về "Present". Nếu không có, trả về "Unknown".

6️⃣ Mục tiêu nghề nghiệp ("career_objective")
Tìm mục "Mục tiêu nghề nghiệp". Trích xuất nội dung. Nếu không có, trả về "No details provided".

⚠️ Quy tắc THEN CHỐT để đảm bảo độ chính xác:

Xử lý định dạng ngày tháng Việt Nam: Nhận dạng và chuyển đổi chính xác các định dạng ngày tháng tiếng Việt theo yêu cầu (MMM, YYYY hoặc YYYY).

Linh hoạt với cấu trúc CV: Hiểu rằng các CV có thể có bố cục và tiêu đề mục khác nhau, cố gắng nhận dạng nội dung dựa trên ngữ cảnh.

']]]]
            
            ]
        ]);

        $data = json_decode($response->getBody(), true);

        $generatedText = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

        $jsonString = GenAIController::cleanJson($generatedText);

        $decodedJson = json_decode($jsonString, true);

        return  $decodedJson;
    }

    static function cleanJson($text)
    {
        // Loại bỏ dấu ```json và ``` nếu có
        $text = preg_replace('/^```json\s*/', '', $text);
        $text = preg_replace('/\s*```$/', '', $text);
        return trim($text);
    }
}

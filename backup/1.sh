
echo "MySQL ma'lumotlar bazasining zaxirasi olinmoqda..."

# Hozirgi sana va vaqtni olish
now=$(date +%Y-%m-%d_%H-%M-%S)

# Oldindan belgilangan qiymatlar
REPO_DIR_API="/home/dev-1/apps/impuls/impuls-api"
DOCKERFILE_API="$REPO_DIR_API/docker-compose.yml"
DB_NAME_API="impulse-api"
DB_PASS_API="impulseMK"
OUTPUT_FILE_API="/home/backup/api/impuls-api-$now.sql"
TAR_FILE_API="/home/backup/api/impuls-api-$now.tar.gz"

# API bazasini zaxiralash
echo "Docker orqali zaxiralash boshlandi..."
docker compose -f $DOCKERFILE_API exec mysql sh -c "mysqldump -uroot -p$DB_PASS_API $DB_NAME_API" > $OUTPUT_FILE_API

if [ $? -eq 0 ]; then
    echo "Zaxira muvaffaqiyatli olindi: $OUTPUT_FILE_API"
else
    echo "Zaxira jarayonida xatolik yuz berdi."
    exit 1
fi

# Faylni siqish
echo "Zaxira faylini siqish boshlandi..."
tar -cvzf $TAR_FILE_API $OUTPUT_FILE_API

if [ $? -eq 0 ]; then
    echo "Fayl siqildi: $TAR_FILE_API"
else
    echo "Siqish jarayonida xatolik yuz berdi."
    exit 1
fi

# Asl SQL faylini o'chirish
rm $OUTPUT_FILE_API

# Telegram API orqali faylni yuborish
echo "Fayl Telegramga yuborilmoqda..."
API_TOKEN="7496415762:AAFr44g8vdOwkb5DX81sPUICbDKQmk4wAQs"
CHAT_ID="813225336"

curl -F chat_id="$CHAT_ID" -F document=@$TAR_FILE_API "https://api.telegram.org/bot$API_TOKEN/sendDocument"

if [ $? -eq 0 ]; then
    echo "Fayl Telegramga muvaffaqiyatli yuborildi."
else
    echo "Telegramga yuborishda xatolik yuz berdi."
    exit 1
fi

# Siqilgan faylni o'chirish (agar kerak bo'lsa)
# rm $TAR_FILE_API

echo "Zaxira jarayoni yakunlandi."



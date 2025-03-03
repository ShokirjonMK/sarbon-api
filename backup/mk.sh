
# Telegram ma'lumotlari
BOT_TOKEN="5216479765:AAEAT2C19Rev5JMBYqhPj_GyTNSVm1-BNYc"
CHAT_ID="813225336"

# Hozirgi sana va vaqtni olish
now=$(date +%Y-%m-%d_%H-%M-%S)

# Oldindan belgilangan qiymatlar
PROJECT_NAME="sarbon"
REPO_DIR_API="/home/dev-1/apps/sarbon/sarbon-api"
DOCKERFILE_API="$REPO_DIR_API/docker-compose.yml"
DB_NAME_API="sarbon_new"
DB_PASS_API="sarsarBON@mk"
OUTPUT_FILE_API="$REPO_DIR_API/backup/$PROJECT_NAME-$now.sql"
ZIP_FILE="$REPO_DIR_API/backup/$PROJECT_NAME-$now.tar.gz"

# API bazasini zaxiralash
echo "Docker orqali zaxiralash boshlandi..."
docker compose -f $DOCKERFILE_API exec mysql sh -c "mysqldump -uroot -p$DB_PASS_API $DB_NAME_API" > $OUTPUT_FILE_API

# 2. Faylni zip qilish
# zip "$TAR_FILE_API" "$OUTPUT_FILE_API"
tar -cvzf $ZIP_FILE $OUTPUT_FILE_API

# 3. Fayl hajmini tekshirish
FILE_SIZE=$(du -m "$ZIP_FILE" | cut -f1)

if (( FILE_SIZE > 49 )); then
    echo "Fayl hajmi katta ($FILE_SIZE MB), bo'laklarga bo'linmoqda..."

    # 4. Faylni 49MB bo‘laklarga ajratish
    split -b 49M "$ZIP_FILE" "${ZIP_FILE}_part_"

    # 5. Har bir bo‘lakni Telegram'ga yuborish
    for file in ${ZIP_FILE}_part_*; do
        curl -F "chat_id=$CHAT_ID" -F "document=@$file" "https://api.telegram.org/bot$BOT_TOKEN/sendDocument"
    done

    # Bo‘laklarni o‘chirish
    rm ${ZIP_FILE}_part_*
else
    # Agar 50MB dan kichik bo‘lsa, to‘g‘ridan-to‘g‘ri yuborish
    curl -F "chat_id=$CHAT_ID" -F "document=@$ZIP_FILE" "https://api.telegram.org/bot$BOT_TOKEN/sendDocument"
fi

# 6. Foydalanilgan fayllarni o‘chirish
rm "$BACKUP_FILE" "$ZIP_FILE"

echo "✅ Backup tayyor va Telegram'ga yuborildi."

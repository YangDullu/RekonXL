pipeline {
    agent any

    environment {
        DEPLOY_USER = 'devops'
        DEPLOY_HOST = '10.54.54.40'
        DEPLOY_PATH = '/var/www/html/upload_csv'
        BACKUP_PATH = '/var/backup/upload_csv'
        TIMESTAMP = """${new Date().format('yyyyMMdd_HHmmss')}"""
    }

    stages {
        stage('Checkout') {
            steps {
                git branch: 'main', url: 'https://github.com/YangDullu/RekonXL.git'
            }
        }

        stage('Changes Overview') {
            steps {
                echo 'Checking changed files...'
                sh '''
                    echo "=== Changed files ==="
                    git fetch origin main
                    git diff --name-only HEAD~1 HEAD || echo "No diff found."
                '''
            }
        }

        stage('Generate Diff Report') {
            steps {
                sh '''
                    mkdir -p build_reports
                    git diff HEAD~1 HEAD > build_reports/diff_report.txt || echo "No diff."
                '''
                archiveArtifacts artifacts: 'build_reports/diff_report.txt', fingerprint: true
            }
        }

        stage('Test') {
            steps {
                echo 'Running PHP lint check...'
                sh 'find . -type f -name "*.php" -exec php -l {} \\;'
            }
        }

        stage('Deploy') {
            steps {
                echo '🚀 Deploying only changed files to production server...'
                sshagent (credentials: ['jenkins-ssh-key']) {
                    sh """
                        # === Get list of changed files ===
                        CHANGED_FILES=\$(git diff --name-only HEAD~1 HEAD)
                        echo "Changed files: \$CHANGED_FILES"
                        
                        # === Backup dan deploy hanya file yang berubah ===
                        if [ -n "\$CHANGED_FILES" ]; then
                            ssh -o StrictHostKeyChecking=no ${DEPLOY_USER}@${DEPLOY_HOST} '
                                mkdir -p ${BACKUP_PATH}
                                BACKUP_DIR="${BACKUP_PATH}/rekonxl_${TIMESTAMP}"
                                echo "📦 Creating backup directory: \$BACKUP_DIR"
                                mkdir -p "\$BACKUP_DIR"
                            '
                            
                            # === Backup dan rsync setiap file yang berubah ===
                            echo "\$CHANGED_FILES" | while read file; do
                                if [ -n "\$file" ] && [ -f "\$file" ]; then
                                    echo "🔄 Processing: \$file"
                                    
                                    # Backup file lama jika ada
                                    ssh -o StrictHostKeyChecking=no ${DEPLOY_USER}@${DEPLOY_HOST} "
                                        if [ -f ${DEPLOY_PATH}/\$file ]; then
                                            echo \"📦 Backing up: \$file\"
                                            mkdir -p \$(dirname \"${BACKUP_PATH}/rekonxl_${TIMESTAMP}/\$file\")
                                            cp ${DEPLOY_PATH}/\$file \"${BACKUP_PATH}/rekonxl_${TIMESTAMP}/\$file\"
                                        fi
                                    "
                                    
                                    # Rsync hanya file yang berubah
                                    rsync -avz \
                                        --no-perms --no-owner --no-group \
                                        -e "ssh -o StrictHostKeyChecking=no" \
                                        "\$file" ${DEPLOY_USER}@${DEPLOY_HOST}:${DEPLOY_PATH}/"\$file"
                                fi
                            done
                        else
                            echo "ℹ️ No files changed, skipping deployment."
                        fi

                        # === (Opsional) Penyesuaian permission untuk file yang di-deploy ===
                        if [ -n "\$CHANGED_FILES" ]; then
                            ssh -o StrictHostKeyChecking=no ${DEPLOY_USER}@${DEPLOY_HOST} '
                                echo "🧩 Checking and adjusting permissions if needed..."

                                # Deteksi user yang dipakai untuk service web
                                if id apache >/dev/null 2>&1; then
                                    WEBUSER="apache"
                                elif id nginx >/dev/null 2>&1; then
                                    WEBUSER="nginx"
                                elif id root >/dev/null 2>&1; then
                                    WEBUSER="root"
                                else
                                    WEBUSER=""
                                fi

                                if [ ! -z "\$WEBUSER" ]; then
                                    echo "🔧 Applying chown/chmod for changed files"
                                    # Apply permission hanya untuk file yang di-deploy
                                    '"'CHANGED_FILES_LIST='"'"'$(echo "\$CHANGED_FILES" | tr "\\n" " ")'"'"'
                                    for file in \$CHANGED_FILES_LIST; do
                                        if [ -f ${DEPLOY_PATH}/\$file ]; then
                                            sudo chown \$WEBUSER:\$WEBUSER ${DEPLOY_PATH}/\$file
                                            sudo chmod 644 ${DEPLOY_PATH}/\$file
                                            # Set permission untuk parent directory jika perlu
                                            DIR=\$(dirname ${DEPLOY_PATH}/\$file)
                                            sudo chown \$WEBUSER:\$WEBUSER \$DIR
                                            sudo chmod 755 \$DIR
                                        fi
                                    done
                                    echo "✅ Permissions adjusted for changed files"
                                else
                                    echo "⚠️ No web user detected, skipping permission adjustment."
                                fi
                            '
                        fi

                        echo "✅ Deployment finished at \$(date)"
                    """
                }
            }
        }
    }
}
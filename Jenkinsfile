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
                    sh '''
                        # === Get list of changed files ===
                        CHANGED_FILES=$(git diff --name-only HEAD~1 HEAD)
                        echo "Changed files: $CHANGED_FILES"
                        
                        # === Backup dan deploy hanya file yang berubah ===
                        if [ -n "$CHANGED_FILES" ]; then
                            # Create backup directory on remote
                            ssh -o StrictHostKeyChecking=no ''' + "${DEPLOY_USER}@${DEPLOY_HOST}" + ''' "mkdir -p ''' + "${BACKUP_PATH}" + '''/rekonxl_''' + "${TIMESTAMP}" + '''"
                            
                            # Process each changed file
                            echo "$CHANGED_FILES" | while read file; do
                                if [ -n "$file" ] && [ -f "$file" ]; then
                                    echo "🔄 Processing: $file"
                                    
                                    # Backup existing file on remote - FIXED QUOTING
                                    ssh -o StrictHostKeyChecking=no ''' + "${DEPLOY_USER}@${DEPLOY_HOST}" + ''' ' "
                                        if [ -f ''' + "${DEPLOY_PATH}" + '''/'"'"'$file'"'"' ]; then
                                            echo \"📦 Backing up: '"'"'$file'"'"'\"
                                            mkdir -p $(dirname \"''' + "${BACKUP_PATH}" + '''/rekonxl_''' + "${TIMESTAMP}" + '''/'"'"'$file'"'"'\" )
                                            cp ''' + "${DEPLOY_PATH}" + '''/'"'"'$file'"'"' \"''' + "${BACKUP_PATH}" + '''/rekonxl_''' + "${TIMESTAMP}" + '''/'"'"'$file'"'''\" 
                                        fi
                                    " '
                                    
                                    # Deploy only the changed file
                                    rsync -avz \
                                        --no-perms --no-owner --no-group \
                                        -e "ssh -o StrictHostKeyChecking=no" \
                                        "$file" ''' + "${DEPLOY_USER}@${DEPLOY_HOST}:${DEPLOY_PATH}" + '''/"$file"
                                fi
                            done
                            
                            # === Permission adjustment untuk file yang di-deploy ===
                            ssh -o StrictHostKeyChecking=no ''' + "${DEPLOY_USER}@${DEPLOY_HOST}" + ''' '
                                echo "🧩 Checking and adjusting permissions for changed files..."
                                
                                # Deteksi user web server
                                if id apache >/dev/null 2>&1; then
                                    WEBUSER="apache"
                                elif id nginx >/dev/null 2>&1; then
                                    WEBUSER="nginx"
                                elif id www-data >/dev/null 2>&1; then
                                    WEBUSER="www-data"
                                elif id root >/dev/null 2>&1; then
                                    WEBUSER="root"
                                else
                                    WEBUSER=""
                                fi
                                
                                if [ ! -z "$WEBUSER" ]; then
                                    echo "🔧 Applying permissions for user: $WEBUSER"
                                    
                                    # Apply permission hanya untuk file dan directory yang di-deploy
                                    CHANGED_FILES_LIST="'"$CHANGED_FILES"'"
                                    for file in $CHANGED_FILES_LIST; do
                                        if [ -f ''' + "${DEPLOY_PATH}" + '''/$file ]; then
                                            sudo chown $WEBUSER:$WEBUSER ''' + "${DEPLOY_PATH}" + '''/$file
                                            
                                            # Permission berbeda berdasarkan user
                                            if [ "$WEBUSER" = "root" ]; then
                                                sudo chmod 600 ''' + "${DEPLOY_PATH}" + '''/$file
                                                echo "🔐 Applied root permissions (600) for: $file"
                                            else
                                                sudo chmod 644 ''' + "${DEPLOY_PATH}" + '''/$file
                                                echo "✅ Applied web permissions (644) for: $file"
                                            fi
                                            
                                            # Juga set permission untuk parent directory
                                            DIR=$(dirname ''' + "${DEPLOY_PATH}" + '''/$file)
                                            sudo chown $WEBUSER:$WEBUSER "$DIR"
                                            
                                            if [ "$WEBUSER" = "root" ]; then
                                                sudo chmod 700 "$DIR"
                                                echo "🔐 Applied root directory permissions (700) for: $DIR"
                                            else
                                                sudo chmod 755 "$DIR"
                                                echo "✅ Applied web directory permissions (755) for: $DIR"
                                            fi
                                        fi
                                    done
                                else
                                    echo "⚠️ No web user detected, skipping permission adjustment."
                                fi
                            '
                        else
                            echo "ℹ️ No files changed, skipping deployment."
                        fi

                        echo "✅ Incremental deployment finished at $(date)"
                    '''
                }
            }
        }
    }
}
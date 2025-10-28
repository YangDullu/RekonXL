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
        echo '🚀 Deploying to production server (with safe backup & permission check)...'
        sshagent (credentials: ['jenkins-ssh-key']) {
            sh """
                ssh -o StrictHostKeyChecking=no ${DEPLOY_USER}@${DEPLOY_HOST} '
                    mkdir -p ${BACKUP_PATH}
                    if [ -d ${DEPLOY_PATH} ]; then
                        BACKUP_DIR="${BACKUP_PATH}/rekonxl_${TIMESTAMP}"
                        echo "📦 Backing up current project to \$BACKUP_DIR"
                        cp -r ${DEPLOY_PATH} \$BACKUP_DIR
                    fi
                '

                rsync -avz \
                    --rsync-path="sudo rsync" \
                    --no-perms --no-owner --no-group \
                    --ignore-errors --stats \
                    -e "ssh -o StrictHostKeyChecking=no" \
                    --exclude '/uploads/**' \
                    --exclude '.git/' \
                    ./ ${DEPLOY_USER}@${DEPLOY_HOST}:${DEPLOY_PATH}/

                ssh -o StrictHostKeyChecking=no ${DEPLOY_USER}@${DEPLOY_HOST} '
                    echo "🧩 Adjusting permissions if needed..."
                    if id apache >/dev/null 2>&1; then
                        WEBUSER="apache"
                    elif id nginx >/dev/null 2>&1; then
                        WEBUSER="nginx"
                    elif id root >/dev/null 2>&1; then
                        WEBUSER="root"
                    fi

                    if [ ! -z "\$WEBUSER" ]; then
                        CURRENT_OWNER=\$(stat -c "%U" ${DEPLOY_PATH})
                        if [ "\$CURRENT_OWNER" != "\$WEBUSER" ]; then
                            echo "🔧 Fixing ownership to \$WEBUSER"
                            sudo chown -R \$WEBUSER:\$WEBUSER ${DEPLOY_PATH}
                        fi
                        sudo find ${DEPLOY_PATH} -type d -exec chmod 755 {} \\;
                        sudo find ${DEPLOY_PATH} -type f -exec chmod 644 {} \\;
                    else
                        echo "⚠️ No web user detected, skipping permission fix."
                    fi
                '

                echo "✅ Deployment finished at \$(date)"
            """
        }
    }
}

    }
}
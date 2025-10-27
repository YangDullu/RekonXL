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
                echo 'Deploying to production server (with backup)...'
                sshagent (credentials: ['jenkins-ssh-key']) {
                    sh """
                        ssh -o StrictHostKeyChecking=no ${DEPLOY_USER}@${DEPLOY_HOST} '
                            mkdir -p ${BACKUP_PATH}
                            if [ -d ${DEPLOY_PATH} ]; then
                                BACKUP_DIR=${BACKUP_PATH}/rekonxl_${TIMESTAMP}
                                echo "Backing up to \$BACKUP_DIR"
                                cp -r ${DEPLOY_PATH} \$BACKUP_DIR
                            fi
                        '
                        rsync -avz \
                            -e "ssh -o StrictHostKeyChecking=no" \
                            --exclude 'uploads/' \
                            --exclude '.git/' \
                            ./ ${DEPLOY_USER}@${DEPLOY_HOST}:${DEPLOY_PATH}/
                    """
                echo "✅ Deployment finished at $(date)"
                }
            }
        }
    }
}
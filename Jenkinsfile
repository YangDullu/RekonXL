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
                # Get changed files
                CHANGED_FILES=\\$(git diff --name-only HEAD~1 HEAD)
                echo "Changed files: \\$CHANGED_FILES"
                
                if [ -n "\\$CHANGED_FILES" ]; then
                    # Create backup directory
                    ssh -o StrictHostKeyChecking=no ${DEPLOY_USER}@${DEPLOY_HOST} "mkdir -p ${BACKUP_PATH}/rekonxl_${TIMESTAMP}"
                    
                    # Process each file
                    echo "\\$CHANGED_FILES" | while read file; do
                        if [ -n "\\$file" ] && [ -f "\\$file" ]; then
                            echo "Processing: \\$file"
                            
                            # Backup file
                            ssh -o StrictHostKeyChecking=no ${DEPLOY_USER}@${DEPLOY_HOST} "
                                if [ -f ${DEPLOY_PATH}/\\$file ]; then
                                    echo \\\\"Backing up: \\$file\\\\"
                                    mkdir -p \\\\$(dirname \\\\"${BACKUP_PATH}/rekonxl_${TIMESTAMP}/\\$file\\\\")
                                    cp ${DEPLOY_PATH}/\\$file \\\\"${BACKUP_PATH}/rekonxl_${TIMESTAMP}/\\$file\\\\"
                                fi
                            "
                            
                            # Deploy file
                            rsync -avz \\
                                --no-perms --no-owner --no-group \\
                                -e "ssh -o StrictHostKeyChecking=no" \\
                                "\\$file" ${DEPLOY_USER}@${DEPLOY_HOST}:${DEPLOY_PATH}/"\\$file"
                        fi
                    done
                    
                    # Set permissions
                    ssh -o StrictHostKeyChecking=no ${DEPLOY_USER}@${DEPLOY_HOST} '
                        # Detect web user
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
                        
                        if [ ! -z "\\$WEBUSER" ]; then
                            echo "Setting permissions for: \\$WEBUSER"
                            sudo find ${DEPLOY_PATH} -type f -exec chmod 644 {} \\;
                            sudo find ${DEPLOY_PATH} -type d -exec chmod 755 {} \\;
                            sudo chown -R \\$WEBUSER:\\$WEBUSER ${DEPLOY_PATH}
                        fi
                    '
                fi
            """
        }
    }
}
    }
}
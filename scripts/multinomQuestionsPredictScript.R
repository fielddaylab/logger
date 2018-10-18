require(nnet)
args <- commandArgs(TRUE)
table <- paste("../logger-data/multinomQuestionsPredict/multinomQuestionsPredictDataForR_", args[1], ".txt", sep="")
formula <- "result~"

for (i in seq_along(args)) {
    if (i > 1) {
        formula <- paste(formula, args[i], sep="+")
    }
}

formula <- as.formula(formula)

questionsData <- read.table(table, sep=",", header=TRUE)
fit <- multinom(formula,data=questionsData)
summary(fit)
#print(pp <- fitted(fit))
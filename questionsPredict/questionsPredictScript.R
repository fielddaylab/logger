args <- commandArgs(TRUE)
table <- paste("questionsPredict/questionsPredictDataForR_", args[1], "_", args[2], ".txt", sep="")
formula <- "result~"

for (i in seq_along(args)) {
    if (i > 1) {
        formula <- paste(formula, args[i], sep="+")
    }
}

formula <- as.formula(formula)

questionsData <- read.table(table, sep=",", header=TRUE)
fit <- glm(formula,data=questionsData,family=binomial(link="logit"))
summary(fit)